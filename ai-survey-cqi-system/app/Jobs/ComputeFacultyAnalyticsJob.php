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
        // 1. Average rating across ALL rating questions
        // ------------------------------------------------------------------
        $ratingValues = Response::whereIn('attempt_id', $attempts->pluck('id'))
            ->whereHas('question', fn ($q) => $q->where('question_type', 'rating'))
            ->whereNotNull('scale_value')
            ->pluck('scale_value');

        $avgRating = $ratingValues->isNotEmpty()
            ? round($ratingValues->avg(), 2)
            : null;

        // ------------------------------------------------------------------
        // 2. Category scores — average rating per question category
        // ------------------------------------------------------------------
        $categoryScores = Response::query()
            ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
            ->join('question_categories', 'survey_questions.category_id', '=', 'question_categories.id')
            ->whereIn('responses.attempt_id', $attempts->pluck('id'))
            ->where('survey_questions.question_type', 'rating')
            ->whereNotNull('responses.scale_value')
            ->groupBy('question_categories.name')
            ->select(
                'question_categories.name as category',
                DB::raw('ROUND(AVG(responses.scale_value), 2) as avg_score')
            )
            ->pluck('avg_score', 'category')
            ->toArray();

        // ------------------------------------------------------------------
        // 3. Sentiment distribution from text responses
        // ------------------------------------------------------------------
        $sentimentCounts = DB::table('response_sentiments')
            ->join('sentiment_types', 'response_sentiments.sentiment_type_id', '=', 'sentiment_types.id')
            ->join('responses', 'response_sentiments.response_id', '=', 'responses.id')
            ->whereIn('responses.attempt_id', $attempts->pluck('id'))
            ->groupBy('sentiment_types.label')
            ->select('sentiment_types.label', DB::raw('COUNT(*) as count'))
            ->pluck('count', 'label')
            ->toArray();

        $totalSentiments = array_sum($sentimentCounts);
        $positivePct = $totalSentiments > 0
            ? round(($sentimentCounts['positive'] ?? 0) / $totalSentiments * 100, 2) : null;
        $neutralPct  = $totalSentiments > 0
            ? round(($sentimentCounts['neutral']  ?? 0) / $totalSentiments * 100, 2) : null;
        $negativePct = $totalSentiments > 0
            ? round(($sentimentCounts['negative'] ?? 0) / $totalSentiments * 100, 2) : null;

        // ------------------------------------------------------------------
        // 4. Top keywords from text responses (simple word frequency)
        // ------------------------------------------------------------------
        $textResponses = Response::whereIn('attempt_id', $attempts->pluck('id'))
            ->whereNotNull('text_response')
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
                'category_scores'            => $categoryScores,
                'top_keywords'               => $topKeywords,
                'last_computed_at'           => now(),
            ]
        );

        Log::info("ComputeFacultyAnalyticsJob: completed for survey {$this->surveyId}.");
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function extractTopKeywords(array $texts, int $limit = 20): array
    {
        $stopWords = [
            'the','a','an','is','it','in','on','at','to','for','of','and','or','but',
            'not','with','this','that','was','are','be','been','has','have','had',
            'do','did','does','i','me','my','we','you','he','she','they','his','her',
            'our','their','its','by','as','if','so','no','yes','very','also','just',
            'about','up','out','all','from','can','will','would','could','should',
            'what','how','when','where','why','who','which','than','more','some',
            'teacher','professor','instructor','subject','course','class', 'sir',
        ];

        $wordCounts = [];

        foreach ($texts as $text) {
            $words = preg_split('/[\s,.\!\?\;\:\"\'\(\)\-\/]+/', strtolower($text));
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) < 3 || in_array($word, $stopWords)) continue;
                $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
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
