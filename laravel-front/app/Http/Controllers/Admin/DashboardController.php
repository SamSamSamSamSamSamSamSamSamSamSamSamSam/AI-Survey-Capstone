<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\User;
use App\Models\Survey;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon; // Added for date formatting

class DashboardController extends Controller
{
    /**
     * Display the main admin dashboard.
     * Most data is cached for 60 seconds to improve performance.
     */
    public function index(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $cacheKey = 'admin_dashboard_' . ($surveyId ?? 'all');
        
        // Fetch surveys for the filter dropdown (not cached)
        $allSurveys = Survey::select('id', 'title')->orderBy('created_at', 'desc')->get();

        // All heavy dashboard data is fetched and cached as a single array
        $data = Cache::remember($cacheKey, 60, function () use ($surveyId) {
            
            $stats = $this->getDashboardStats($surveyId);
            $performanceData = $this->getFacultyPerformanceData($surveyId);
            $chartData = $this->getMonthlyChartData($surveyId);

            // Merge all data arrays into one payload for the cache
            return [
                ...$stats,
                ...$performanceData,
                ...$chartData,
            ];
        });

        return view('admin.dashboard', array_merge($data, ['allSurveys' => $allSurveys]));
    }

    /**
     * Get the main KPI card statistics for the dashboard.
     */
    private function getDashboardStats($surveyId): array
    {
        // 1. General Rating Stats
        $ratingValues = Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->pluck('response')
            ->map(fn($v) => is_numeric($v) ? (float)$v : null)
            ->filter();

        $ratingStats = $this->calculateRatingStats($ratingValues);

        // 2. Overall Sentiment Totals
        $sentimentTotals = Response::select('sentiment_label', DB::raw('count(*) as cnt'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->whereNotNull('sentiment_label')
            ->groupBy('sentiment_label')
            ->pluck('cnt', 'sentiment_label')
            ->toArray();

        $totalSentiment = array_sum($sentimentTotals);
        $overallPositivePct = $totalSentiment 
            ? number_format((($sentimentTotals['positive'] ?? 0) / $totalSentiment) * 100, 1) 
            : 'N/A';

        // 3. Participation
        $distinctEvaluators = Response::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->distinct('evaluator_id')
            ->count('evaluator_id');

        $eligibleEvaluators = null;
        try {
            $eligibleEvaluators = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        } catch (\Throwable $e) {
             // Fail gracefully
        }
        
        $participationPct = $eligibleEvaluators 
            ? round($distinctEvaluators / max(1, $eligibleEvaluators) * 100, 1) 
            : null;

        return [
            'mean' => $ratingStats['mean'],
            'median' => $ratingStats['median'],
            'mode' => $ratingStats['mode'],
            'stddev' => $ratingStats['stddev'],
            'rating_count' => $ratingStats['count'],
            'sentimentTotals' => $sentimentTotals,
            'overallPositivePct' => $overallPositivePct, 
            'distinct_evaluators' => $distinctEvaluators,
            'eligible_evaluators' => $eligibleEvaluators,
            'participation_pct' => $participationPct,
        ];
    }

    /**
     * Get data for the "Top Performers" and "Sentiment Breakdown" tables.
     */
    private function getFacultyPerformanceData($surveyId): array
    {
        // 1. Get sentiment data grouped by person
        $sentimentRows = Response::select('evaluatee_id', 'sentiment_label', DB::raw('count(*) as cnt'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->whereNotNull('sentiment_label')
            ->groupBy('evaluatee_id', 'sentiment_label')
            ->get()
            ->groupBy('evaluatee_id');

        // 2. Get top performers (rating)
        $topPerformersQuery = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select('responses.evaluatee_id', DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'), DB::raw('count(*) as cnt'))
            ->where('questions.type', 'rating')
            ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
            ->groupBy('responses.evaluatee_id')
            ->having('cnt', '>=', 3)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();
        
        // 3. N+1 Query Fix: Get all evaluatee names in one query
        $evaluateeIds = $sentimentRows->keys()
            ->merge($topPerformersQuery->pluck('evaluatee_id'))
            ->unique();
        $evaluateeNames = User::whereIn('id', $evaluateeIds)->pluck('name', 'id');

        // 4. Process Sentiment Per Person
        $sentimentPerPerson = [];
        foreach ($sentimentRows as $evaluateeId => $group) {
            $total = $group->sum('cnt');
            $labels = $group->pluck('cnt', 'sentiment_label');
            
            $sentimentPerPerson[] = [
                'evaluatee_id' => $evaluateeId,
                'name' => $evaluateeNames->get($evaluateeId) ?? "User {$evaluateeId}",
                'total' => $total,
                'positive_pct' => $total ? round(($labels['positive'] ?? 0) / $total * 100, 1) : 0,
                'negative_pct' => $total ? round(($labels['negative'] ?? 0) / $total * 100, 1) : 0,
                'neutral_pct' => $total ? round(($labels['neutral'] ?? 0) / $total * 100, 1) : 0,
            ];
        }
        
        // 5. Process Top Performers
        $topPerformers = $topPerformersQuery->map(function ($row) use ($sentimentRows, $evaluateeNames) {
            $sentimentGroup = $sentimentRows->get($row->evaluatee_id);
            $positivePct = 0;

            if ($sentimentGroup) {
                $totalSent = $sentimentGroup->sum('cnt');
                $positive = $sentimentGroup->first(fn($r) => $r->sentiment_label === 'positive')->cnt ?? 0; 
                $positivePct = $totalSent ? round($positive / $totalSent * 100, 1) : 0;
            }
            
            return [
                'evaluatee_id' => $row->evaluatee_id,
                'name' => $evaluateeNames->get($row->evaluatee_id) ?? "User {$row->evaluatee_id}",
                'avg_rating' => round((float)$row->avg_rating, 3),
                'count' => $row->cnt,
                'positive_pct' => $positivePct,
            ];
        })->toArray();

        return [
            'sentimentPerPerson' => collect($sentimentPerPerson)->sortByDesc('total')->slice(0, 10)->values()->toArray(),
            'topPerformers' => $topPerformers,
        ];
    }

    /**
     * Get the data needed for the monthly trend chart.
     */
    private function getMonthlyChartData($surveyId): array
    {
        // 1. Get monthly average ratings
        $monthlyRatings = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select(DB::raw("DATE_FORMAT(responses.created_at, '%Y-%m') as month"), DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'))
            ->where('questions.type', 'rating')
            ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Use the rating months as the definitive labels for the chart
        $monthlyLabels = $monthlyRatings->pluck('month')->toArray();
        $monthlyAvg = $monthlyRatings->pluck('avg_rating')->map(fn($v) => round((float)$v, 3))->toArray();

        // 2. Get monthly sentiment percentages
        $monthlySent = DB::table('responses')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), 'sentiment_label', DB::raw('count(*) as cnt'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->whereNotNull('sentiment_label')
            ->groupBy('month', 'sentiment_label')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $monthlyPositivePct = [];
        foreach ($monthlySent as $month => $group) {
            $total = $group->sum('cnt');
            $positive = $group->first(fn($r) => $r->sentiment_label === 'positive')->cnt ?? 0;
            $monthlyPositivePct[$month] = $total ? round($positive / $total * 100, 1) : 0;
        }
        
        // 3. Map sentiment data to rating labels and format labels for display
        $monthlyPosSeries = array_map(fn($month) => $monthlyPositivePct[$month] ?? 0, $monthlyLabels);
        $formattedMonthlyLabels = array_map(fn($month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'), $monthlyLabels);

        return [
            'monthlyLabels' => $formattedMonthlyLabels,
            'monthlyAvg' => $monthlyAvg,
            'monthlyPositivePct' => array_values($monthlyPosSeries),
        ];
    }

    /**
     * Show detailed analysis per question.
     */
    public function questionAnalysis(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $qWord = trim($request->query('q', '')); 

        if ($surveyId) {
            $survey = Survey::find($surveyId);
            $questions = Question::where('survey_id', $surveyId)->orderBy('order')->get();

            $matchedIds = [];
            if ($qWord) {
                $matchedQuestionIdsFromText = Question::where('survey_id', $surveyId)
                    ->where('question_text', 'like', "%{$qWord}%")
                    ->pluck('id')
                    ->toArray();

                $matchedQuestionIdsFromResponses = DB::table('responses')
                    ->join('questions', 'responses.question_id', '=', 'questions.id')
                    ->where('questions.survey_id', $surveyId)
                    ->where('responses.response', 'like', "%{$qWord}%")
                    ->pluck('question_id')
                    ->toArray();

                $matchedIds = array_values(array_unique(array_merge($matchedQuestionIdsFromText, $matchedQuestionIdsFromResponses)));
            }
        } else {
            $questions = Question::with('survey')
                ->when($qWord, function ($query) use ($qWord) {
                    $query->where('question_text', 'like', "%{$qWord}%")
                        ->orWhereHas('responses', function ($r) use ($qWord) {
                            $r->where('response', 'like', "%{$qWord}%");
                        });
                })
                ->orderBy('survey_id')->orderBy('order')->get();

            $survey = null;
            $matchedIds = $questions->pluck('id')->toArray();
        }

        $stats = [];
        foreach ($questions as $q) {
            $isMatched = in_array($q->id, $matchedIds);

            if ($q->type === 'rating') {
                $rows = Response::where('question_id', $q->id)
                    ->when($surveyId, fn($q2) => $q2->where('survey_id', $surveyId))
                    ->pluck('response')
                    ->map(fn($v) => is_numeric($v) ? (float)$v : null)
                    ->filter();

                $ratingStats = $this->calculateRatingStats($rows);

                $distribution = array_fill(1, 5, 0);
                $byValue = Response::select('response', DB::raw('count(*) as cnt'))
                    ->where('question_id', $q->id)
                    ->when($surveyId, fn($q2) => $q2->where('survey_id', $surveyId))
                    ->groupBy('response')
                    ->get();
                
                foreach ($byValue as $r) {
                    $key = (int)$r->response;
                    if ($key >= 1 && $key <= 5) $distribution[$key] = (int)$r->cnt;
                }

                $stats[] = [
                    'question' => $q,
                    'type' => 'rating',
                    'count' => $ratingStats['count'],
                    'mean' => $ratingStats['mean'],
                    'median' => $ratingStats['median'],
                    'stddev' => $ratingStats['stddev'],
                    'distribution' => $distribution,
                    'matched' => $isMatched,
                ];
            } else {
                $rows = Response::where('question_id', $q->id)
                    ->when($surveyId, fn($q2) => $q2->where('survey_id', $surveyId))
                    ->select('response', 'created_at', 'sentiment_label', 'sentiment_score')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $responsesList = $rows->map(function ($r) {
                    return [
                        'response' => $r->response,
                        'created_at' => $r->created_at?->toDateTimeString(),
                        'sentiment_label' => $r->sentiment_label,
                        'sentiment_score' => $r->sentiment_score,
                        'evaluator' => 'Anonymous',
                    ];
                })->toArray();

                $topWords = $this->generateWordFrequency($rows->pluck('response'), 40);

                $stats[] = [
                    'question' => $q,
                    'type' => 'text',
                    'count' => count($responsesList),
                    'top_words' => $topWords,
                    'responses' => $responsesList,
                    'matched' => $isMatched,
                ];
            }
        }

        return view('admin.analysis.questionAnalysis', [
            'stats' => $stats,
            'surveyId' => $surveyId,
            'qWord' => $qWord,
            'survey' => $survey,
        ]);
    }

    /**
     * Show all responses and stats for a single evaluatee.
     */
    public function evaluateeDetails($evaluateeId, Request $request)
    {
        $surveyId = $request->query('survey_id');
        $evaluatee = User::findOrFail($evaluateeId);

        $responses = Response::with('question')
            ->where('evaluatee_id', $evaluateeId)
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'question' => $r->question?->question_text,
                    'type' => $r->question?->type,
                    'response' => $r->response,
                    'sentiment_label' => $r->sentiment_label,
                    'sentiment_score' => $r->sentiment_score,
                    'created_at' => $r->created_at->toDateTimeString(),
                    'evaluator' => 'Anonymous',
                ];
            });

        $ratingValues = Response::where('evaluatee_id', $evaluateeId)
            ->whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->pluck('response')
            ->map(fn($v) => is_numeric($v) ? (float)$v : null)
            ->filter();

        $metrics = $this->calculateRatingStats($ratingValues);

        $sent = Response::select('sentiment_label', DB::raw('count(*) as cnt'))
            ->where('evaluatee_id', $evaluateeId)
            ->whereNotNull('sentiment_label')
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->groupBy('sentiment_label')
            ->pluck('cnt', 'sentiment_label')
            ->toArray();

        $metrics['positive'] = $sent['positive'] ?? 0;
        $metrics['negative'] = $sent['negative'] ?? 0;
        $metrics['neutral'] = $sent['neutral'] ?? 0;

        return view('admin.evaluatee.evaluateeDetails', [
            'evaluatee' => $evaluatee,
            'responses' => $responses,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Generate data for the word cloud visualization.
     */
    public function wordCloud(Request $request)
    {
        $surveyId = $request->query('survey_id');

        $texts = Response::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->whereHas('question', fn($qq) => $qq->where('type', 'text'))
            ->pluck('response')
            ->map(fn($t) => trim($t))
            ->filter();

        $topWords = $this->generateWordFrequency($texts, 150);

        $questions = Question::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->where('type', 'text')
            ->get(['id', 'survey_id', 'question_text']);

        $wordLinks = [];
        foreach (array_keys($topWords) as $w) {
            $foundSurveyId = null;
            
            $row = DB::table('responses')
                ->join('questions', 'responses.question_id', '=', 'questions.id')
                ->select('questions.survey_id')
                ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
                ->where('questions.type', 'text')
                ->where('responses.response', 'like', "%{$w}%")
                ->first();

            if ($row && isset($row->survey_id)) {
                $foundSurveyId = $row->survey_id;
            }
            
            if (!$foundSurveyId) {
                $qMatch = $questions->first(fn($qq) => mb_stripos($qq->question_text, $w) !== false);
                if ($qMatch) $foundSurveyId = $qMatch->survey_id;
            }

            $params = $foundSurveyId ? ['survey_id' => $foundSurveyId, 'q' => $w] : ['q' => $w];
            $wordLinks[$w] = route('admin.analysis.questionAnalysis', $params);
        }

        return view('admin.analysis.wordCloud', [
            'words' => $topWords,
            'questions' => $questions,
            'surveyId' => $surveyId,
            'wordLinks' => $wordLinks,
        ]);
    }

    /**
     * Show a list of surveys to choose from for question analysis.
     */
    public function questionAnalysisList(Request $request)
    {
        $surveys = Survey::select('id', 'title', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.analysis.surveys', ['surveys' => $surveys]);
    }

    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    /**
     * Calculates descriptive statistics for a collection of ratings.
     */
    private function calculateRatingStats(Collection $ratingValues): array
    {
        $count = $ratingValues->count();
        if ($count === 0) {
            return ['count' => 0, 'mean' => null, 'median' => null, 'mode' => null, 'stddev' => null];
        }

        $mean = round($ratingValues->avg(), 3);
        
        $sorted = $ratingValues->sort()->values();
        $mid = (int) floor(($count - 1) / 2);
        $median = ($count % 2) 
            ? $sorted[$mid] 
            : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
        
        $mode = $ratingValues->countBy()->sortDesc()->keys()->first();
        
        $variance = $ratingValues->reduce(fn($c, $x) => $c + pow($x - $mean, 2), 0) / $count;
        $stddev = round(sqrt($variance), 3);

        return compact('count', 'mean', 'median', 'mode', 'stddev');
    }

    /**
     * Generates a word frequency map from a collection of text.
     */
    private function generateWordFrequency(Collection $texts, int $limit = 150): array
    {
        $stop = $this->stopwords();
        $freq = [];
        
        foreach ($texts as $txt) {
            $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $txt ?? ''));
            $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($words as $w) {
                if (mb_strlen($w) < 3 || isset($stop[$w])) {
                    continue;
                }
                $freq[$w] = ($freq[$w] ?? 0) + 1;
            }
        }
        
        arsort($freq);
        return array_slice($freq, 0, $limit, true);
    }

    /**
     * Returns a hash map of common English stopwords for fast lookups.
     */
    private function stopwords(): array
    {
        static $stopWords = null;
        
        if ($stopWords === null) {
            $list = [
                'the','and','for','with','this','that','from','have','were','their','they','them','will','your',
                'are','was','but','not','you','has','had','its','his','her','which','what','when','where','how',
                'our','also','can','could','should','would','there','been','about','than','then','each','into',
                'more','other','some','such','only','these','those','very','because','during','without','within',
                'instructor', 'teacher', 'faculty', 'professor'
            ];
            $stopWords = array_combine($list, $list);
        }

        return $stopWords;
    }
}