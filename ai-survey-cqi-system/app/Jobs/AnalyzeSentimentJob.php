<?php

namespace App\Jobs;

use App\Models\FacultyAnalytics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Computes and persists analytics for a faculty member's subject offering.
 *
 * Aggregates ALL survey responses across every survey tied to the given
 * course offering — not just a single survey — so the analytics represent
 * the full picture of that subject.
 *
 * Dispatched after any survey under the offering is submitted.
 */
class ComputeFacultyAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 180;

    public function __construct(
        public readonly string $offeringId
    ) {}

    // -----------------------------------------------------------------------
    // Entry point
    // -----------------------------------------------------------------------

    public function handle(): void
    {
        // Resolve offering + faculty in one small query
        $offering = DB::table('course_offerings')
            ->where('id', $this->offeringId)
            ->select('id', 'teacher_id')
            ->first();

        if (! $offering) {
            Log::warning("ComputeFacultyAnalyticsJob: offering {$this->offeringId} not found.");
            return;
        }

        // ------------------------------------------------------------------
        // Collect all submitted attempt IDs across every survey for this
        // offering — this is the single source-of-truth for "responses from
        // this subject".
        // ------------------------------------------------------------------
        $attemptSubquery = DB::table('survey_attempts')
            ->whereIn(
                'survey_id',
                DB::table('surveys')
                    ->where('offering_id', $this->offeringId)
                    ->select('id')
            )
            ->whereNotNull('submitted_at')
            ->select('id');

        $responseCount = (clone $attemptSubquery)->count();

        if ($responseCount === 0) {
            Log::info("ComputeFacultyAnalyticsJob: no submissions for offering {$this->offeringId}, skipping.");
            return;
        }

        // ------------------------------------------------------------------
        // 1. Rating statistics
        // ------------------------------------------------------------------
        $ratingStats = $this->computeRatingStats($attemptSubquery);

        // ------------------------------------------------------------------
        // 2. Category scores — avg rating per question category
        // ------------------------------------------------------------------
        $categoryScores = $this->computeCategoryScores($attemptSubquery);

        // ------------------------------------------------------------------
        // 3. Sentiment distribution
        // ------------------------------------------------------------------
        $sentimentDistribution = $this->computeSentimentDistribution($attemptSubquery);

        // ------------------------------------------------------------------
        // 4. Top keywords from open-ended text responses
        // ------------------------------------------------------------------
        $topKeywords = $this->computeTopKeywords($attemptSubquery);

        // ------------------------------------------------------------------
        // 5. Persist — one row per offering
        // ------------------------------------------------------------------
        DB::transaction(function () use ($offering, $responseCount, $ratingStats, $categoryScores, $sentimentDistribution, $topKeywords) {
            FacultyAnalytics::updateOrCreate(
                ['offering_id' => $this->offeringId],
                [
                    'faculty_id'      => $offering->teacher_id,
                    'response_count'  => $responseCount,

                    'rating_stats' => [
                        'avg'     => $ratingStats['avg'],
                        'median'  => $ratingStats['median'],
                        'mode'    => $ratingStats['mode'],
                        'std_dev' => $ratingStats['std_dev'],
                        'min'     => $ratingStats['min'],
                        'max'     => $ratingStats['max'],
                    ],

                    'category_scores' => $categoryScores,

                    'sentiment' => $sentimentDistribution,

                    'top_keywords'    => $topKeywords,
                    'last_computed_at' => now(),
                ]
            );
        });

        Log::info("ComputeFacultyAnalyticsJob: completed for offering {$this->offeringId}.", [
            'response_count' => $responseCount,
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Aggregate AVG, STDDEV, MIN, MAX, MODE, and MEDIAN for all rating
     * responses belonging to the given attempts.
     *
     * Uses a subquery for MEDIAN so we never interpolate IDs into raw SQL.
     */
    private function computeRatingStats(\Illuminate\Database\Query\Builder $attemptSubquery): array
    {
        $base = DB::table('responses')
            ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
            ->whereIn('responses.attempt_id', (clone $attemptSubquery)->select('id'))
            ->where('survey_questions.question_type', 'rating')
            ->whereNotNull('responses.scale_value');

        $agg = (clone $base)->selectRaw(
            'COUNT(*) as total,
             AVG(responses.scale_value)        as avg_val,
             STDDEV_POP(responses.scale_value) as std_dev,
             MIN(responses.scale_value)        as min_val,
             MAX(responses.scale_value)        as max_val'
        )->first();

        $total = (int) ($agg->total ?? 0);

        if ($total === 0) {
            return ['avg' => 0, 'median' => 0, 'mode' => 0, 'std_dev' => 0, 'min' => 0, 'max' => 0];
        }

        // Mode
        $mode = (clone $base)
            ->select('responses.scale_value', DB::raw('COUNT(*) as freq'))
            ->groupBy('responses.scale_value')
            ->orderByDesc('freq')
            ->first()
            ?->scale_value ?? 0;

        // Median — fully parameterised, no string interpolation
        $isEven = ($total % 2 === 0);
        $offset = $isEven ? ($total / 2) - 1 : (int) floor($total / 2);
        $limit  = $isEven ? 2 : 1;

        $medianValue = (clone $base)
            ->orderBy('responses.scale_value')
            ->offset((int) $offset)
            ->limit((int) $limit)
            ->avg('responses.scale_value');

        return [
            'avg'     => round((float) ($agg->avg_val ?? 0), 2),
            'median'  => round((float) ($medianValue ?? 0), 2),
            'mode'    => (float) $mode,
            'std_dev' => round((float) ($agg->std_dev ?? 0), 2),
            'min'     => (float) ($agg->min_val ?? 0),
            'max'     => (float) ($agg->max_val ?? 0),
        ];
    }

    /**
     * Average rating score broken down by question category.
     * Returns ['Category Name' => avg_score, ...]
     */
    private function computeCategoryScores(\Illuminate\Database\Query\Builder $attemptSubquery): array
    {
        return DB::table('responses')
            ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
            ->join('question_categories', 'survey_questions.category_id', '=', 'question_categories.id')
            ->whereIn('responses.attempt_id', (clone $attemptSubquery)->select('id'))
            ->where('survey_questions.question_type', 'rating')
            ->whereNotNull('responses.scale_value')
            ->groupBy('question_categories.id', 'question_categories.name')
            ->select(
                'question_categories.name as category',
                DB::raw('ROUND(AVG(responses.scale_value), 2) as avg_score')
            )
            ->pluck('avg_score', 'category')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    /**
     * Count of positive / neutral / negative sentiments and their percentages.
     *
     * Returns:
     * [
     *   'counts'   => ['positive' => n, 'neutral' => n, 'negative' => n],
     *   'percents' => ['positive' => %, 'neutral' => %, 'negative' => %],
     * ]
     */
    private function computeSentimentDistribution(\Illuminate\Database\Query\Builder $attemptSubquery): array
    {
        $counts = DB::table('response_sentiments')
            ->join('sentiment_types', 'response_sentiments.sentiment_type_id', '=', 'sentiment_types.id')
            ->join('responses', 'response_sentiments.response_id', '=', 'responses.id')
            ->whereIn('responses.attempt_id', (clone $attemptSubquery)->select('id'))
            ->groupBy('sentiment_types.id', 'sentiment_types.label')
            ->select('sentiment_types.label', DB::raw('COUNT(*) as count'))
            ->pluck('count', 'label')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $total = array_sum($counts);

        $pct = fn (string $label): float => $total > 0
            ? round((($counts[$label] ?? 0) / $total) * 100, 2)
            : 0.0;

        return [
            'counts' => [
                'positive' => $counts['positive'] ?? 0,
                'neutral'  => $counts['neutral']  ?? 0,
                'negative' => $counts['negative'] ?? 0,
            ],
            'percents' => [
                'positive' => $pct('positive'),
                'neutral'  => $pct('neutral'),
                'negative' => $pct('negative'),
            ],
        ];
    }

    /**
     * Extract the most frequently used meaningful words from open-ended
     * text responses. Pulls at most $fetchLimit rows from the DB and
     * counts at most $wordCap individual word tokens for performance.
     */
    private function computeTopKeywords(
        \Illuminate\Database\Query\Builder $attemptSubquery,
        int $topN      = 20,
        int $fetchLimit = 1000,
        int $wordCap    = 10_000
    ): array {
        $texts = DB::table('responses')
            ->whereIn('attempt_id', (clone $attemptSubquery)->select('id'))
            ->whereNotNull('text_response')
            ->limit($fetchLimit)
            ->pluck('text_response');

        return $this->extractTopKeywords($texts->all(), $topN, $wordCap);
    }

    /**
     * Pure word-frequency extraction — no DB interaction.
     *
     * @param  string[] $texts
     * @return string[]  Top $limit words, sorted by descending frequency
     */
    private function extractTopKeywords(array $texts, int $limit = 20, int $wordCap = 10_000): array
    {
        static $stopWords = null;
        $stopWords ??= array_flip([
            'the','a','an','is','it','in','on','at','to','for','of','and','or','but',
            'not','with','this','that','was','are','be','been','has','have','had',
            'do','did','does','i','me','my','we','you','he','she','they','his','her',
            'our','their','its','by','as','if','so','no','yes','very','also','just',
            'about','up','out','all','from','can','will','would','could','should',
            'what','how','when','where','why','who','which','than','more','some',
            'teacher','professor','instructor','subject','course','class','sir',
        ]);

        $wordCounts = [];
        $processed  = 0;

        foreach ($texts as $text) {
            preg_match_all('/\b[a-z]{3,}\b/i', strtolower((string) $text), $matches);

            foreach ($matches[0] as $word) {
                if (isset($stopWords[$word])) {
                    continue;
                }

                $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;

                if (++$processed >= $wordCap) {
                    break 2;
                }
            }
        }

        arsort($wordCounts);

        return array_keys(array_slice($wordCounts, 0, $limit, true));
    }

    // -----------------------------------------------------------------------
    // Failure handler
    // -----------------------------------------------------------------------

    public function failed(\Throwable $exception): void
    {
        Log::error("ComputeFacultyAnalyticsJob failed for offering {$this->offeringId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}