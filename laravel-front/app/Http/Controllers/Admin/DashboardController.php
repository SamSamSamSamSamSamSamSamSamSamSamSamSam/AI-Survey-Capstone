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

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $cacheKey = 'admin_dashboard_' . ($surveyId ?? 'all');

        $data = Cache::remember($cacheKey, 60, function () use ($surveyId) {
            // Rating stats (rating questions only)
            $ratingQuery = Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId));

            $ratingValues = $ratingQuery->pluck('response')
                ->map(fn($v) => is_numeric($v) ? (float)$v : null)
                ->filter();

            $count = $ratingValues->count();
            $mean = $count ? round($ratingValues->avg(), 3) : null;
            $median = null;
            if ($count) {
                $sorted = $ratingValues->sort()->values();
                $mid = (int) floor(($count - 1) / 2);
                $median = ($count % 2) ? $sorted[$mid] : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
            }
            $mode = $count ? $ratingValues->countBy()->sortDesc()->keys()->first() : null;
            $stddev = null;
            if ($count) {
                $avg = $mean;
                $variance = $ratingValues->reduce(fn($c, $x) => $c + pow($x - $avg, 2), 0) / $count;
                $stddev = round(sqrt($variance), 3);
            }

            // Participation
            $distinctEvaluators = Response::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->distinct('evaluator_id')
                ->count('evaluator_id');

            $eligibleEvaluators = null;
            try {
                $eligibleEvaluators = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
            } catch (\Throwable $e) {
                $eligibleEvaluators = null;
            }
            $participationPct = $eligibleEvaluators ? round($distinctEvaluators / max(1, $eligibleEvaluators) * 100, 1) : null;

            // Sentiment per evaluatee
            $sentimentRows = Response::select('evaluatee_id', 'sentiment_label', DB::raw('count(*) as cnt'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->groupBy('evaluatee_id', 'sentiment_label')
                ->get()
                ->groupBy('evaluatee_id');

            $sentimentPerPerson = [];
            foreach ($sentimentRows as $evaluateeId => $group) {
                $total = $group->sum('cnt');
                $labels = $group->pluck('cnt', 'sentiment_label')->toArray();
                $user = User::find($evaluateeId);
                $positive = $labels['positive'] ?? 0;
                $negative = $labels['negative'] ?? 0;
                $neutral = $labels['neutral'] ?? 0;
                $sentimentPerPerson[] = [
                    'evaluatee_id' => $evaluateeId,
                    'name' => $user?->name ?? "User {$evaluateeId}",
                    'total' => $total,
                    'positive_pct' => $total ? round($positive / $total * 100, 1) : 0,
                    'negative_pct' => $total ? round($negative / $total * 100, 1) : 0,
                    'neutral_pct' => $total ? round($neutral / $total * 100, 1) : 0,
                ];
            }

            // Top performers (min 3 responses)
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

            $topPerformers = $topPerformersQuery->map(function ($row) {
                $user = User::find($row->evaluatee_id);
                return [
                    'evaluatee_id' => $row->evaluatee_id,
                    'name' => $user?->name ?? "User {$row->evaluatee_id}",
                    'avg_rating' => round((float)$row->avg_rating, 3),
                    'count' => $row->cnt,
                ];
            })->toArray();

            // Monthly series
            $monthlyRatings = DB::table('responses')
                ->join('questions', 'responses.question_id', '=', 'questions.id')
                ->select(DB::raw("DATE_FORMAT(responses.created_at, '%Y-%m') as month"), DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'))
                ->where('questions.type', 'rating')
                ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlyLabels = $monthlyRatings->pluck('month')->toArray();
            $monthlyAvg = $monthlyRatings->pluck('avg_rating')->map(fn($v) => round((float)$v, 3))->toArray();

            $monthlySent = DB::table('responses')
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), 'sentiment_label', DB::raw('count(*) as cnt'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->groupBy('month', 'sentiment_label')
                ->orderBy('month')
                ->get()
                ->groupBy('month');

            $monthlyPositivePct = [];
            foreach ($monthlySent as $month => $group) {
                $total = $group->sum('cnt');
                $positive = $group->firstWhere('sentiment_label', 'positive')->cnt ?? 0;
                $monthlyPositivePct[$month] = $total ? round($positive / $total * 100, 2) : 0;
            }
            $monthlyPosSeries = array_map(fn($m) => $monthlyPositivePct[$m] ?? 0, $monthlyLabels);

            return [
                'mean' => $mean,
                'median' => $median,
                'mode' => $mode,
                'stddev' => $stddev,
                'rating_count' => $count,
                'distinct_evaluators' => $distinctEvaluators,
                'eligible_evaluators' => $eligibleEvaluators,
                'participation_pct' => $participationPct,
                'sentimentPerPerson' => $sentimentPerPerson,
                'topPerformers' => $topPerformers,
                'monthlyLabels' => $monthlyLabels,
                'monthlyAvg' => $monthlyAvg,
                'monthlyPositivePct' => array_values($monthlyPosSeries),
            ];
        });

        return view('admin.dashboard', $data);
    }

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

                $count = $rows->count();
                $mean = $count ? round($rows->avg(), 3) : null;

                $median = null;
                if ($count) {
                    $sorted = $rows->sort()->values();
                    $mid = (int) floor(($count - 1) / 2);
                    $median = ($count % 2) ? $sorted[$mid] : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
                }

                $stddev = null;
                if ($count) {
                    $variance = $rows->reduce(fn($c, $x) => $c + pow($x - $mean, 2), 0) / $count;
                    $stddev = round(sqrt($variance), 3);
                }

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
                    'count' => $count,
                    'mean' => $mean,
                    'median' => $median,
                    'stddev' => $stddev,
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

                $stop = $this->stopwords();
                $freq = [];
                foreach ($rows->pluck('response') as $txt) {
                    $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $txt));
                    $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($words as $w) {
                        if (mb_strlen($w) < 3) continue;
                        if (isset($stop[$w])) continue;
                        $freq[$w] = ($freq[$w] ?? 0) + 1;
                    }
                }
                arsort($freq);
                $topWords = array_slice($freq, 0, 40, true);

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

        $ratingStats = Response::where('evaluatee_id', $evaluateeId)
            ->whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->pluck('response')
            ->map(fn($v) => is_numeric($v) ? (float)$v : null)
            ->filter();

        $metrics = [];
        $count = $ratingStats->count();
        $metrics['count'] = $count;
        $metrics['mean'] = $count ? round($ratingStats->avg(), 3) : null;
        if ($count) {
            $sorted = $ratingStats->sort()->values();
            $mid = (int) floor(($count - 1) / 2);
            $metrics['median'] = ($count % 2) ? $sorted[$mid] : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
        } else {
            $metrics['median'] = null;
        }

        $sent = Response::select('sentiment_label', DB::raw('count(*) as cnt'))
            ->where('evaluatee_id', $evaluateeId)
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->groupBy('sentiment_label')
            ->get()
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

    public function wordCloud(Request $request)
    {
        $surveyId = $request->query('survey_id');

        $texts = Response::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->whereHas('question', fn($qq) => $qq->where('type', 'text'))
            ->pluck('response')
            ->map(fn($t) => trim($t))
            ->filter();

        $stop = $this->stopwords();
        $freq = [];
        foreach ($texts as $txt) {
            $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $txt));
            $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $w) {
                if (mb_strlen($w) < 3) continue;
                if (isset($stop[$w])) continue;
                $freq[$w] = ($freq[$w] ?? 0) + 1;
            }
        }
        arsort($freq);
        $top = array_slice($freq, 0, 150, true);

        $questions = Question::when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->where('type', 'text')
            ->get(['id', 'survey_id', 'question_text']);

        $wordLinks = [];
        foreach (array_keys($top) as $w) {
            $found = null;
            $row = DB::table('responses')
                ->join('questions', 'responses.question_id', '=', 'questions.id')
                ->select('questions.survey_id')
                ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
                ->where('responses.response', 'like', "%{$w}%")
                ->first();

            if ($row && isset($row->survey_id)) {
                $found = $row->survey_id;
            } else {
                $qMatch = $questions->first(fn($qq) => mb_stripos($qq->question_text, $w) !== false);
                if ($qMatch) $found = $qMatch->survey_id;
            }

            $wordLinks[$w] = $found
                ? route('admin.analysis.questionAnalysis', ['survey_id' => $found, 'q' => $w])
                : route('admin.analysis.questionAnalysis', ['q' => $w]);
        }

        return view('admin.analysis.wordCloud', [
            'words' => $top,
            'questions' => $questions,
            'surveyId' => $surveyId,
            'wordLinks' => $wordLinks,
        ]);
    }

    private function stopwords(): array
    {
        $list = [
            'the','and','for','with','this','that','from','have','were','their','they','them','will','your',
            'are','was','but','not','you','has','had','its','his','her','which','what','when','where','how',
            'our','also','can','could','should','would','there','been','about','than','then','each','into',
            'more','other','some','such','only','these','those','very','because','during','without','within'
        ];
        return array_combine($list, $list);
    }

    public function questionAnalysisList(Request $request)
    {
        $surveys = Survey::select('id', 'title', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.analysis.surveys', ['surveys' => $surveys]);
    }
}