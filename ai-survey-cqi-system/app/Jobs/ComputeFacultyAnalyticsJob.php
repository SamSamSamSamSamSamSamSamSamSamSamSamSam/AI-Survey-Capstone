<?php

namespace App\Jobs;

use App\Models\FacultyAnalytics;
use App\Models\Response;
use App\Models\SurveyAttempt;
use App\Models\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ComputeFacultyAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 180;

    public function __construct(
        public readonly string $surveyId
    ) {}

    public function handle(): void
    {
        Cache::forget("faculty_analytics_categories_{$this->surveyId}");
        Cache::forget("faculty_analytics_sentiment_{$this->surveyId}");

        $survey = Survey::with([
            'offering.teacher',
            'questions.responses.sentiment.sentimentType',
            'attempts' => fn ($q) => $q->whereNotNull('submitted_at'),
        ])->find($this->surveyId);

        if (! $survey) {
            Log::warning("ComputeFacultyAnalyticsJob: survey {$this->surveyId} not found.");
            return;
        }

        $attempts = $survey->attempts;
        $responseCount = $attempts->count();

        if ($responseCount === 0) {
            Log::info("ComputeFacultyAnalyticsJob: no submissions for survey {$this->surveyId}, skipping.");
            return;
        }

        // ------------------------------------------------------------------
        // 1. Quantitative Ratings & Descriptive Statistics (Optimized)
        // ------------------------------------------------------------------
        $attemptIds = $attempts->pluck('id')->toArray();
        
        $ratingStats = DB::table('responses')
            ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
            ->whereIn('responses.attempt_id', $attemptIds)
            ->where('survey_questions.question_type', 'rating')
            ->whereNotNull('responses.scale_value')
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(responses.scale_value) as avg_val'),
                DB::raw('STDDEV_POP(responses.scale_value) as std_dev'),
                DB::raw('MIN(responses.scale_value) as min_val'),
                DB::raw('MAX(responses.scale_value) as max_val')
            )
            ->first();

        $avgRating = $ratingStats->count > 0 ? round($ratingStats->avg_val ?? 0, 2) : 0;
        $stdDev    = $ratingStats->count > 0 ? round($ratingStats->std_dev ?? 0, 2) : 0;
        
        // Mode: get most common rating value
        $mode = $ratingStats->count > 0 ? DB::table('responses')
            ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
            ->whereIn('responses.attempt_id', $attemptIds)
            ->where('survey_questions.question_type', 'rating')
            ->whereNotNull('responses.scale_value')
            ->select('responses.scale_value', DB::raw('COUNT(*) as freq'))
            ->groupBy('responses.scale_value')
            ->orderByDesc('freq')
            ->first()?->scale_value ?? 0 : 0;
        
        // Median calculation - FIXED: Calculate offset in PHP to prevent MySQL Syntax Error
        $median = 0;
        if ($ratingStats && $ratingStats->count > 0) {
            $totalCount = $ratingStats->count;
            $isEven = ($totalCount % 2 == 0);
            
            // For median: if odd, take middle 1; if even, take middle 2 and average them
            $limit = $isEven ? 2 : 1;
            $offset = $isEven ? ($totalCount / 2) - 1 : floor($totalCount / 2);

            $medianResult = DB::selectOne(
                "SELECT AVG(scale_value) as median FROM (
                    SELECT responses.scale_value
                    FROM responses
                    JOIN survey_questions ON responses.survey_question_id = survey_questions.id
                    WHERE responses.attempt_id IN (" . implode(',', array_map('intval', $attemptIds)) . ")
                    AND survey_questions.question_type = 'rating'
                    AND responses.scale_value IS NOT NULL
                    ORDER BY responses.scale_value
                    LIMIT ? OFFSET ?
                ) as sub", [(int)$limit, (int)$offset]
            );

            $median = $medianResult?->median ?? 0;
        }
        
        $median = is_numeric($median) ? round($median, 2) : (($ratingStats->min_val ?? 0) + ($ratingStats->max_val ?? 0)) / 2;

        // ------------------------------------------------------------------
        // 2. Category scores — average rating per question category (Cached)
        // ------------------------------------------------------------------
        $categoryScores = Cache::remember(
            "faculty_analytics_categories_{$survey->id}",
            3600, 
            function () use ($attemptIds) {
                return DB::table('responses')
                    ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
                    ->join('question_categories', 'survey_questions.category_id', '=', 'question_categories.id')
                    ->whereIn('responses.attempt_id', $attemptIds)
                    ->where('survey_questions.question_type', 'rating')
                    ->whereNotNull('responses.scale_value')
                    ->groupBy('question_categories.name', 'question_categories.id')
                    ->select(
                        'question_categories.name as category',
                        DB::raw('ROUND(AVG(responses.scale_value), 2) as avg_score')
                    )
                    ->pluck('avg_score', 'category')
                    ->toArray();
            }
        );

        // ------------------------------------------------------------------
        // 3. Sentiment distribution (Batch query)
        // ------------------------------------------------------------------
        $sentimentCounts = Cache::remember(
            "faculty_analytics_sentiment_{$survey->id}",
            3600,
            function () use ($attemptIds) {
                return DB::table('response_sentiments')
                    ->join('sentiment_types', 'response_sentiments.sentiment_type_id', '=', 'sentiment_types.id')
                    ->join('responses', 'response_sentiments.response_id', '=', 'responses.id')
                    ->whereIn('responses.attempt_id', $attemptIds)
                    ->groupBy('sentiment_types.label', 'sentiment_types.id')
                    ->select('sentiment_types.label', DB::raw('COUNT(*) as count'))
                    ->pluck('count', 'label')
                    ->toArray();
            }
        );

        $totalSentiments = array_sum($sentimentCounts);
        $positivePct = $totalSentiments > 0 ? round(($sentimentCounts['positive'] ?? 0) / $totalSentiments * 100, 2) : 0;
        $neutralPct  = $totalSentiments > 0 ? round(($sentimentCounts['neutral']  ?? 0) / $totalSentiments * 100, 2) : 0;
        $negativePct = $totalSentiments > 0 ? round(($sentimentCounts['negative'] ?? 0) / $totalSentiments * 100, 2) : 0;

        // ------------------------------------------------------------------
        // 4. Top keywords (Batch query with limit)
        // ------------------------------------------------------------------
        $textResponses = Response::whereIn('attempt_id', $attemptIds)
            ->whereNotNull('text_response')
            ->select('text_response')
            ->limit(1000) 
            ->pluck('text_response');

        $topKeywords = $this->extractTopKeywords($textResponses->toArray());

        // ------------------------------------------------------------------
        // 5. Upsert faculty_analytics
        // ------------------------------------------------------------------
        FacultyAnalytics::updateOrCreate(
            ['survey_id' => $survey->id],
            [
                'offering_id'                => $survey->offering_id,
                'faculty_id'                 => $survey->offering->teacher_id,
                'avg_rating'                 => $avgRating,
                'response_count'             => $responseCount,
                'positive_sentiment_percent' => $positivePct,
                'neutral_sentiment_percent'  => $neutralPct,
                'negative_sentiment_percent' => $negativePct,
                
                'category_scores'            => array_merge($categoryScores, [
                    '_overall_stats' => [
                        'median'  => $median,
                        'mode'    => $mode,
                        'std_dev' => $stdDev,
                    ]
                ]),
                
                'top_keywords'               => $topKeywords,
                'last_computed_at'           => now(),
            ]
        );

        Log::info("ComputeFacultyAnalyticsJob: completed for survey {$this->surveyId}.");
    }

    private function extractTopKeywords(array $texts, int $limit = 20): array
    {
        $stopWords = array_flip([
            'the','a','an','is','it','in','on','at','to','for','of','and','or','but',
            'not','with','this','that','was','are','be','been','has','have','had',
            'do','did','does','i','me','my','we','you','he','she','they','his','her',
            'our','their','its','by','as','if','so','no','yes','very','also','just',
            'about','up','out','all','from','can','will','would','could','should',
            'what','how','when','where','why','who','which','than','more','some',
            'teacher','professor','instructor','subject','course','class', 'sir',
        ]);

        $wordCounts = [];
        $maxWords = 10000;
        $processedWords = 0;

        foreach ($texts as $text) {
            if ($processedWords >= $maxWords) break;
            
            preg_match_all('/\b[a-z]{3,}\b/i', strtolower($text), $matches);
            
            foreach ($matches[0] as $word) {
                if ($processedWords >= $maxWords) break;
                if (isset($stopWords[$word])) continue;
                
                $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
                $processedWords++;
            }
        }

        arsort($wordCounts);
        return array_keys(array_slice($wordCounts, 0, $limit, true));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ComputeFacultyAnalyticsJob failed for survey {$this->surveyId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}