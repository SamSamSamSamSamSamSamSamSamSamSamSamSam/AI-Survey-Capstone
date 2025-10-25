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
use Illuminate\Support\Collection; // Added for type-hinting

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
        
        // 1. Fetch all surveys for the filter dropdown (Not cached)
        $allSurveys = Survey::select('id', 'title')->orderBy('created_at', 'desc')->get();

        // 2. Fetch all dashboard data, caching the results
        $data = Cache::remember($cacheKey, 60, function () use ($surveyId) {
            
            // --- General Rating Stats ---
            $ratingQuery = Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId));

            $ratingValues = $ratingQuery->pluck('response')
                ->map(fn($v) => is_numeric($v) ? (float)$v : null)
                ->filter(); // Remove nulls

            $ratingStats = $this->calculateRatingStats($ratingValues);

            // --- Overall Sentiment Totals (For the new metric card) ---
            $sentimentTotals = Response::select('sentiment_label', DB::raw('count(*) as cnt'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->whereNotNull('sentiment_label') // Only count valid sentiments
                ->groupBy('sentiment_label')
                ->pluck('cnt', 'sentiment_label')
                ->toArray();

            // Calculate Overall Positive Pct (This was previously in the Blade file)
            $totalSentiment = array_sum($sentimentTotals);
            $overallPositivePct = $totalSentiment 
                ? number_format((($sentimentTotals['positive'] ?? 0) / $totalSentiment) * 100, 1) 
                : 'N/A';

            // --- Participation ---
            $distinctEvaluators = Response::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->distinct('evaluator_id')
                ->count('evaluator_id');

            $eligibleEvaluators = null;
            try {
                // This assumes 'student' role is the only one eligible.
                // Wrapped in try/catch in case 'roles' relationship fails.
                $eligibleEvaluators = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
            } catch (\Throwable $e) {
                $eligibleEvaluators = null; // Fail gracefully
            }
            $participationPct = $eligibleEvaluators 
                ? round($distinctEvaluators / max(1, $eligibleEvaluators) * 100, 1) 
                : null;

            // --- Sentiment per evaluatee (Used for the breakdown table) ---
            $sentimentRows = Response::select('evaluatee_id', 'sentiment_label', DB::raw('count(*) as cnt'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->whereNotNull('sentiment_label')
                ->groupBy('evaluatee_id', 'sentiment_label')
                ->get()
                ->groupBy('evaluatee_id'); // Group by person in PHP

            // --- Top performers (min 3 responses) ---
            $topPerformersQuery = DB::table('responses')
                ->join('questions', 'responses.question_id', '=', 'questions.id')
                ->select('responses.evaluatee_id', DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'), DB::raw('count(*) as cnt'))
                ->where('questions.type', 'rating')
                ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
                ->groupBy('responses.evaluatee_id')
                ->having('cnt', '>=', 3) // Only include faculty with 3+ ratings
                ->orderByDesc('avg_rating')
                ->limit(10)
                ->get();
            
            // --- N+1 Query Fix ---
            // Get all unique evaluatee IDs from both sentiment and top performer lists
            $evaluateeIds = $sentimentRows->keys()
                ->merge($topPerformersQuery->pluck('evaluatee_id'))
                ->unique();

            // Fetch all users in ONE query
            $evaluateeNames = User::whereIn('id', $evaluateeIds)->pluck('name', 'id');

            // --- Process Sentiment Per Person ---
            $sentimentPerPerson = [];
            foreach ($sentimentRows as $evaluateeId => $group) {
                $total = $group->sum('cnt');
                $labels = $group->pluck('cnt', 'sentiment_label')->toArray();
                
                $positive = $labels['positive'] ?? 0;
                $negative = $labels['negative'] ?? 0;
                $neutral = $labels['neutral'] ?? 0;
                
                $sentimentPerPerson[] = [
                    'evaluatee_id' => $evaluateeId,
                    'name' => $evaluateeNames->get($evaluateeId) ?? "User {$evaluateeId}", // Use pre-fetched name
                    'total' => $total,
                    'positive_pct' => $total ? round($positive / $total * 100, 1) : 0,
                    'negative_pct' => $total ? round($negative / $total * 100, 1) : 0,
                    'neutral_pct' => $total ? round($neutral / $total * 100, 1) : 0,
                ];
            }
            // Sort by total and slice to top 10 for the breakdown table
            $sentimentPerPerson = collect($sentimentPerPerson)->sortByDesc('total')->slice(0, 10)->values()->toArray();

            // --- Process Top Performers ---
            $topPerformers = $topPerformersQuery->map(function ($row) use ($sentimentRows, $evaluateeNames) {
                $sentimentGroup = $sentimentRows->get($row->evaluatee_id);
                $positivePct = 0;

                if ($sentimentGroup) {
                    $totalSent = $sentimentGroup->sum('cnt');
                    $positive = $sentimentGroup->firstWhere('sentiment_label', 'positive')->cnt ?? 0;
                    $positivePct = $totalSent ? round($positive / $totalSent * 100, 1) : 0;
                }
                
                return [
                    'evaluatee_id' => $row->evaluatee_id,
                    'name' => $evaluateeNames->get($row->evaluatee_id) ?? "User {$row->evaluatee_id}", // Use pre-fetched name
                    'avg_rating' => round((float)$row->avg_rating, 3),
                    'count' => $row->cnt,
                    'positive_pct' => $positivePct, // Added positive sentiment percent
                ];
            })->toArray();


            // --- Monthly Time Series (For Chart) ---
            
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
                ->groupBy('month'); // Group by month in PHP

            $monthlyPositivePct = [];
            foreach ($monthlySent as $month => $group) {
                $total = $group->sum('cnt');
                $positive = $group->firstWhere('sentiment_label', 'positive')->cnt ?? 0;
                $monthlyPositivePct[$month] = $total ? round($positive / $total * 100, 1) : 0;
            }
            
            // 3. Map sentiment data to rating labels
            // This ensures both datasets have the same length and align by month.
            // If a month has ratings but no sentiment, it will show 0%.
            $monthlyPosSeries = array_map(fn($month) => $monthlyPositivePct[$month] ?? 0, $monthlyLabels);

            // --- Return all data for the view ---
            return [
                'mean' => $ratingStats['mean'],
                'median' => $ratingStats['median'],
                'mode' => $ratingStats['mode'],
                'stddev' => $ratingStats['stddev'],
                'rating_count' => $ratingStats['count'],
                'sentimentTotals' => $sentimentTotals,
                'overallPositivePct' => $overallPositivePct, // Pass the new variable
                'distinct_evaluators' => $distinctEvaluators,
                'eligible_evaluators' => $eligibleEvaluators,
                'participation_pct' => $participationPct,
                'sentimentPerPerson' => $sentimentPerPerson, // Already sliced to top 10
                'topPerformers' => $topPerformers,
                'monthlyLabels' => $monthlyLabels,
                'monthlyAvg' => $monthlyAvg,
                'monthlyPositivePct' => array_values($monthlyPosSeries),
            ];
        });

        // Pass both cached data and the non-cached surveys list to the view
        return view('admin.dashboard', array_merge($data, ['allSurveys' => $allSurveys]));
    }

    /**
     * Show detailed analysis per question.
     */
    public function questionAnalysis(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $qWord = trim($request->query('q', '')); // Search keyword

        // --- 1. Filter Questions based on survey and keyword ---
        if ($surveyId) {
            // Specific survey selected
            $survey = Survey::find($surveyId);
            $questions = Question::where('survey_id', $surveyId)->orderBy('order')->get();

            $matchedIds = [];
            if ($qWord) {
                // Find questions where the question text matches
                $matchedQuestionIdsFromText = Question::where('survey_id', $surveyId)
                    ->where('question_text', 'like', "%{$qWord}%")
                    ->pluck('id')
                    ->toArray();

                // Find questions where any response text matches
                $matchedQuestionIdsFromResponses = DB::table('responses')
                    ->join('questions', 'responses.question_id', '=', 'questions.id')
                    ->where('questions.survey_id', $surveyId)
                    ->where('responses.response', 'like', "%{$qWord}%")
                    ->pluck('question_id')
                    ->toArray();

                $matchedIds = array_values(array_unique(array_merge($matchedQuestionIdsFromText, $matchedQuestionIdsFromResponses)));
            }
        } else {
            // "All Surveys" selected
            $questions = Question::with('survey')
                ->when($qWord, function ($query) use ($qWord) {
                    // Search both question text and response text
                    $query->where('question_text', 'like', "%{$qWord}%")
                        ->orWhereHas('responses', function ($r) use ($qWord) {
                            $r->where('response', 'like', "%{$qWord}%");
                        });
                })
                ->orderBy('survey_id')->orderBy('order')->get();

            $survey = null;
            $matchedIds = $questions->pluck('id')->toArray(); // All found questions are "matched"
        }

        // --- 2. Calculate stats for each question ---
        $stats = [];
        foreach ($questions as $q) {
            $isMatched = in_array($q->id, $matchedIds);

            if ($q->type === 'rating') {
                // --- Calculate stats for RATING questions ---
                $rows = Response::where('question_id', $q->id)
                    ->when($surveyId, fn($q2) => $q2->where('survey_id', $surveyId))
                    ->pluck('response')
                    ->map(fn($v) => is_numeric($v) ? (float)$v : null)
                    ->filter();

                // Use the helper method
                $ratingStats = $this->calculateRatingStats($rows);

                // Get the distribution (e.g., 5 '1's, 10 '5's)
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
                // --- Calculate stats for TEXT questions ---
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
                        'evaluator' => 'Anonymous', // Assuming anonymity
                    ];
                })->toArray();

                // Use the helper method
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

        // Get all responses for this user
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

        // Get all rating values
        $ratingValues = Response::where('evaluatee_id', $evaluateeId)
            ->whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->pluck('response')
            ->map(fn($v) => is_numeric($v) ? (float)$v : null)
            ->filter();

        // Use the helper method
        $metrics = $this->calculateRatingStats($ratingValues);

        // Get sentiment counts
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

        // Get all text responses
        $texts = Response::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->whereHas('question', fn($qq) => $qq->where('type', 'text'))
            ->pluck('response')
            ->map(fn($t) => trim($t))
            ->filter();

        // Use the helper method to get top 150 words
        $topWords = $this->generateWordFrequency($texts, 150);

        // Get text questions to link words back to
        $questions = Question::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->where('type', 'text')
            ->get(['id', 'survey_id', 'question_text']);

        // Generate links for each word to the questionAnalysis page
        $wordLinks = [];
        foreach (array_keys($topWords) as $w) {
            $foundSurveyId = null;
            
            // Find the first survey where this word appears in a response
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
            
            // If not found in responses, check if it's in a question text
            if (!$foundSurveyId) {
                $qMatch = $questions->first(fn($qq) => mb_stripos($qq->question_text, $w) !== false);
                if ($qMatch) $foundSurveyId = $qMatch->survey_id;
            }

            // Build the appropriate route
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
     *
     * @param Collection $ratingValues A collection of numbers.
     * @return array
     */
    private function calculateRatingStats(Collection $ratingValues): array
    {
        $count = $ratingValues->count();
        $mean = null;
        $median = null;
        $mode = null;
        $stddev = null;

        if ($count > 0) {
            $mean = round($ratingValues->avg(), 3);
            
            // Median
            $sorted = $ratingValues->sort()->values();
            $mid = (int) floor(($count - 1) / 2);
            $median = ($count % 2) 
                ? $sorted[$mid] 
                : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
            
            // Mode
            $mode = $ratingValues->countBy()->sortDesc()->keys()->first();
            
            // Standard Deviation
            $variance = $ratingValues->reduce(fn($c, $x) => $c + pow($x - $mean, 2), 0) / $count;
            $stddev = round(sqrt($variance), 3);
        }

        return compact('count', 'mean', 'median', 'mode', 'stddev');
    }

    /**
     * Generates a word frequency map from a collection of text.
     *
     * @param Collection $texts A collection of strings.
     * @param int $limit The number of top words to return.
     * @return array ['word' => count]
     */
    private function generateWordFrequency(Collection $texts, int $limit = 150): array
    {
        $stop = $this->stopwords();
        $freq = [];
        
        foreach ($texts as $txt) {
            // Clean the text: lowercase, remove punctuation
            $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $txt ?? ''));
            // Split into words
            $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($words as $w) {
                if (mb_strlen($w) < 3) continue; // Skip short words
                if (isset($stop[$w])) continue; // Skip stopwords
                
                $freq[$w] = ($freq[$w] ?? 0) + 1;
            }
        }
        
        arsort($freq); // Sort by frequency, high to low
        return array_slice($freq, 0, $limit, true);
    }

    /**
     * Returns a hash map of common English stopwords for fast lookups.
     *
     * @return array
     */
    private function stopwords(): array
    {
        $list = [
            'the','and','for','with','this','that','from','have','were','their','they','them','will','your',
            'are','was','but','not','you','has','had','its','his','her','which','what','when','where','how',
            'our','also','can','could','should','would','there','been','about','than','then','each','into',
            'more','other','some','such','only','these','those','very','because','during','without','within',
            'instructor', 'teacher', 'faculty', 'professor' // Domain-specific stopwords
        ];
        // array_combine creates a hash map (e.g., 'the' => 'the') for fast isset() checks
        return array_combine($list, $list);
    }
}