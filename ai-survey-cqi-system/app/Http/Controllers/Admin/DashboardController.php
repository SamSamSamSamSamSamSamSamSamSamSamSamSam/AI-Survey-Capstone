<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\User;
use App\Models\Survey;
use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacherId  = $request->query('teacher_id');
        $semesterId = $request->query('semester_id');
        $cacheKey   = 'admin_dashboard_' . ($teacherId ?? 'all') . '_' . ($semesterId ?? 'all');

        // Teachers (evaluatees) for the filter dropdown
        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->orderBy('name')
            ->get();

        // Semesters for the filter dropdown
        $semesters = Semester::orderByDesc('academic_year')
            ->orderByDesc('semester_number')
            ->get();

        $data = Cache::remember($cacheKey, 60, function () use ($teacherId, $semesterId) {
            $stats           = $this->getDashboardStats($teacherId, $semesterId);
            $performanceData = $this->getFacultyPerformanceData($teacherId, $semesterId);
            $chartData       = $this->getMonthlyChartData($teacherId, $semesterId);
            $categoryScores  = $this->getCategoryScores($teacherId, $semesterId);

            return [
                ...$stats,
                ...$performanceData,
                ...$chartData,
                'categoryScores' => $categoryScores,
            ];
        });

        return view('admin.dashboard', array_merge($data, [
            'teachers'         => $teachers,
            'semesters'        => $semesters,
            'selectedTeacher'  => $teacherId,
            'selectedSemester' => $semesterId,
        ]));
    }

    // ── KPI SUMMARY ────────────────────────────────────────────────────────────
    private function getDashboardStats($teacherId, $semesterId): array
    {
        $ratingValues = Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->when($teacherId,  fn($q) => $q->where('evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('semester_id',  $semesterId))
            ->pluck('response')
            ->map(fn($v) => is_numeric($v) ? (float)$v : null)
            ->filter();

        $ratingStats = $this->calculateRatingStats($ratingValues);

        $sentimentTotals = Response::select('sentiment_label', DB::raw('count(*) as cnt'))
            ->when($teacherId,  fn($q) => $q->where('evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('semester_id',  $semesterId))
            ->whereNotNull('sentiment_label')
            ->groupBy('sentiment_label')
            ->pluck('cnt', 'sentiment_label')
            ->toArray();

        $totalSent = array_sum($sentimentTotals);
        $overallPositivePct = $totalSent
            ? number_format((($sentimentTotals['positive'] ?? 0) / $totalSent) * 100, 1)
            : 'N/A';

        $distinctEvaluators = Response::when($teacherId,  fn($q) => $q->where('evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('semester_id',  $semesterId))
            ->distinct('evaluator_id')
            ->count('evaluator_id');

        $eligibleEvaluators = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();

        $participationPct = $eligibleEvaluators
            ? round($distinctEvaluators / max(1, $eligibleEvaluators) * 100, 1)
            : null;

        $ratingDist = array_fill(1, 5, 0); // [1=>0, 2=>0, 3=>0, 4=>0, 5=>0]
        foreach ($ratingValues as $v) {
            $score = (int) round($v);
            if ($score >= 1 && $score <= 5) {
                $ratingDist[$score]++;
            }
        }

        return [
            'mean'                => $ratingStats['mean'],
            'median'              => $ratingStats['median'],
            'mode'                => $ratingStats['mode'],
            'stddev'              => $ratingStats['stddev'],
            'rating_count'        => $ratingStats['count'],
            'sentimentTotals'     => $sentimentTotals,
            'overallPositivePct'  => $overallPositivePct,
            'distinct_evaluators' => $distinctEvaluators,
            'eligible_evaluators' => $eligibleEvaluators,
            'participation_pct'   => $participationPct,
            'ratingDistribution'  => array_values($ratingDist),
        ];
    }

    // ── TOP FACULTY + SENTIMENT ────────────────────────────────────────────────
    private function getFacultyPerformanceData($teacherId, $semesterId): array
    {
        $sentimentRows = Response::select('evaluatee_id', 'sentiment_label', DB::raw('count(*) as cnt'))
            ->when($teacherId,  fn($q) => $q->where('evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('semester_id',  $semesterId))
            ->whereNotNull('sentiment_label')
            ->groupBy('evaluatee_id', 'sentiment_label')
            ->get()
            ->groupBy('evaluatee_id');

        $topPerformersQuery = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select(
                'responses.evaluatee_id',
                DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'),
                DB::raw('count(*) as cnt')
            )
            ->where('questions.type', 'rating')
            ->when($teacherId,  fn($q) => $q->where('responses.evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('responses.semester_id',  $semesterId))
            ->groupBy('responses.evaluatee_id')
            ->having('cnt', '>=', 3)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        $evaluateeIds   = $sentimentRows->keys()->merge($topPerformersQuery->pluck('evaluatee_id'))->unique();
        $evaluateeNames = User::whereIn('id', $evaluateeIds)->pluck('name', 'id');

        $sentimentPerPerson = [];
        foreach ($sentimentRows as $evaluateeId => $group) {
            $total  = $group->sum('cnt');
            $labels = $group->pluck('cnt', 'sentiment_label');
            $sentimentPerPerson[] = [
                'evaluatee_id' => $evaluateeId,
                'name'         => $evaluateeNames[$evaluateeId] ?? "User {$evaluateeId}",
                'total'        => $total,
                'positive_pct' => $total ? round(($labels['positive'] ?? 0) / $total * 100, 1) : 0,
                'negative_pct' => $total ? round(($labels['negative'] ?? 0) / $total * 100, 1) : 0,
                'neutral_pct'  => $total ? round(($labels['neutral']  ?? 0) / $total * 100, 1) : 0,
            ];
        }

        $topPerformers = $topPerformersQuery->map(function ($row) use ($sentimentRows, $evaluateeNames) {
            $sentGroup   = $sentimentRows->get($row->evaluatee_id);
            $positivePct = 0;
            if ($sentGroup) {
                $total       = $sentGroup->sum('cnt');
                $pos         = $sentGroup->first(fn($r) => $r->sentiment_label === 'positive')->cnt ?? 0;
                $positivePct = $total ? round($pos / $total * 100, 1) : 0;
            }
            return [
                'evaluatee_id' => $row->evaluatee_id,
                'name'         => $evaluateeNames[$row->evaluatee_id] ?? "User {$row->evaluatee_id}",
                'avg_rating'   => round($row->avg_rating, 3),
                'count'        => $row->cnt,
                'positive_pct' => $positivePct,
            ];
        })->toArray();

        return [
            'sentimentPerPerson' => collect($sentimentPerPerson)->sortByDesc('total')->take(10)->values()->toArray(),
            'topPerformers'      => $topPerformers,
        ];
    }

    // ── CATEGORY SCORES ────────────────────────────────────────────────────────
    private function getCategoryScores($teacherId, $semesterId)
    {
        $rows = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select('questions.category', DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_score'))
            ->where('questions.type', 'rating')
            ->when($teacherId,  fn($q) => $q->where('responses.evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('responses.semester_id',  $semesterId))
            ->groupBy('questions.category')
            ->get();

        return $rows->map(fn($r) => [
            'category' => $r->category ?? 'Uncategorized',
            'avg'      => round($r->avg_score, 2),
        ])->toArray();
    }

    // ── MONTHLY TRENDS ─────────────────────────────────────────────────────────
    private function getMonthlyChartData($teacherId, $semesterId): array
    {
        $monthlyRatings = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select(
                DB::raw("DATE_FORMAT(responses.created_at, '%Y-%m') as month"),
                DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating')
            )
            ->where('questions.type', 'rating')
            ->when($teacherId,  fn($q) => $q->where('responses.evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('responses.semester_id',  $semesterId))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyLabels = $monthlyRatings->pluck('month')->toArray();
        $monthlyAvg    = $monthlyRatings->pluck('avg_rating')->map(fn($v) => round($v, 3))->toArray();

        $monthlySent = DB::table('responses')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                'sentiment_label',
                DB::raw('count(*) as cnt')
            )
            ->when($teacherId,  fn($q) => $q->where('evaluatee_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('semester_id',  $semesterId))
            ->whereNotNull('sentiment_label')
            ->groupBy('month', 'sentiment_label')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $monthlyPositivePct = [];
        foreach ($monthlySent as $month => $g) {
            $total = $g->sum('cnt');
            $pos   = $g->first(fn($r) => $r->sentiment_label === 'positive')->cnt ?? 0;
            $monthlyPositivePct[$month] = $total ? round($pos / $total * 100, 1) : 0;
        }

        $monthlyPosSeries = array_map(fn($m) => $monthlyPositivePct[$m] ?? 0, $monthlyLabels);
        $formattedLabels  = array_map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'), $monthlyLabels);

        return [
            'monthlyLabels'      => $formattedLabels,
            'monthlyAvg'         => $monthlyAvg,
            'monthlyPositivePct' => array_values($monthlyPosSeries),
        ];
    }

    // ── HELPERS ────────────────────────────────────────────────────────────────
    private function calculateRatingStats(Collection $ratingValues): array
    {
        $count = $ratingValues->count();
        if ($count === 0) {
            return ['count' => 0, 'mean' => null, 'median' => null, 'mode' => null, 'stddev' => null];
        }

        $mean     = round($ratingValues->avg(), 3);
        $sorted   = $ratingValues->sort()->values();
        $mid      = (int) floor(($count - 1) / 2);
        $median   = ($count % 2) ? $sorted[$mid] : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
        $mode     = $ratingValues->countBy()->sortDesc()->keys()->first();
        $variance = $ratingValues->reduce(fn($c, $x) => $c + pow($x - $mean, 2), 0) / $count;
        $stddev   = round(sqrt($variance), 3);

        return compact('count', 'mean', 'median', 'mode', 'stddev');
    }

    // ── OTHER PAGES ────────────────────────────────────────────────────────────
    public function questionAnalysisList()
    {
        $surveys = Survey::select('id', 'title', 'created_at')->latest()->get();
        return view('admin.analysis.surveys', compact('surveys'));
    }

    public function questionAnalysis(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $qWord    = $request->query('q');
        $survey   = $surveyId ? Survey::with('questions')->find($surveyId) : null;

        $questions = $survey
            ? $survey->questions
            : \App\Models\Question::with('responses.evaluator')->get();

        $stats = [];
        foreach ($questions as $question) {
            $responses = $question->responses;
            $matched   = $qWord && str_contains(strtolower($question->question_text), strtolower($qWord));

            if ($question->type === 'rating') {
                $vals = $responses->pluck('response')->map(fn($v) => is_numeric($v) ? (float)$v : null)->filter();
                $dist = array_fill(1, 5, 0);
                foreach ($vals as $v) { if (isset($dist[(int)$v])) $dist[(int)$v]++; }

                $stats[] = [
                    'question'     => $question,
                    'type'         => 'rating',
                    'count'        => $vals->count(),
                    'mean'         => $vals->count() ? round($vals->avg(), 2) : null,
                    'median'       => $vals->count() ? $vals->sort()->values()[intdiv($vals->count() - 1, 2)] : null,
                    'stddev'       => $vals->count() > 1 ? round(sqrt($vals->reduce(fn($c, $x) => $c + pow($x - $vals->avg(), 2), 0) / $vals->count()), 2) : null,
                    'distribution' => $dist,
                    'matched'      => $matched,
                ];
            } else {
                $texts    = $responses->pluck('response');
                $topWords = $this->topWords($texts->all());
                $stats[]  = [
                    'question'  => $question,
                    'type'      => 'text',
                    'count'     => $texts->count(),
                    'top_words' => $topWords,
                    'responses' => $responses->map(fn($r) => [
                        'created_at'      => $r->created_at->toDateTimeString(),
                        'response'        => $r->response,
                        'sentiment_label' => $r->sentiment_label,
                        'sentiment_score' => $r->sentiment_score,
                        'evaluator'       => $r->evaluator?->name ?? 'Unknown',
                    ])->toArray(),
                    'matched' => $matched,
                ];
            }
        }

        return view('admin.analysis.questionAnalysis', compact('stats', 'survey', 'surveyId', 'qWord'));
    }

    public function wordCloud(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $texts    = Response::whereHas('question', fn($q) => $q->where('type', 'text'))
            ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
            ->pluck('response');

        $words     = $this->topWords($texts->all(), 100);
        $wordLinks = [];
        foreach (array_keys($words) as $w) {
            $wordLinks[$w] = route('admin.analysis.questionAnalysis', ['survey_id' => $surveyId, 'q' => $w]);
        }

        return view('admin.analysis.wordCloud', compact('words', 'wordLinks', 'surveyId'));
    }

    public function evaluateeDetails($id)
    {
        $evaluatee = User::findOrFail($id);
        $responses = Response::with(['question', 'evaluator'])
            ->where('evaluatee_id', $id)
            ->latest()
            ->get()
            ->map(fn($r) => [
                'created_at'      => $r->created_at->toDateTimeString(),
                'question'        => $r->question?->question_text ?? 'N/A',
                'response'        => $r->response,
                'sentiment_label' => $r->sentiment_label,
                'sentiment_score' => $r->sentiment_score,
                'evaluator'       => $r->evaluator?->name ?? 'Unknown',
            ]);

        $ratingVals = $responses->filter(fn($r) => is_numeric($r['response']))->pluck('response');
        $metrics    = [
            'count'  => $ratingVals->count(),
            'mean'   => $ratingVals->count() ? round($ratingVals->avg(), 2) : null,
            'median' => $ratingVals->count() ? $ratingVals->sort()->values()[intdiv($ratingVals->count() - 1, 2)] : null,
        ];

        return view('admin.evaluatee.evaluateeDetails', compact('evaluatee', 'responses', 'metrics'));
    }

    private function topWords(array $texts, int $limit = 150): array
    {
        $stop = array_flip([
            'the','and','for','with','this','that','from','have','were','their','they','them','will','your',
            'are','was','but','not','you','has','had','its','his','her','which','what','when','where','how',
            'our','also','can','could','should','would','there','been','about','than','then','each','into',
            'more','other','some','such','only','these','those','very','because','during','without','within',
            'instructor','teacher','faculty','professor',
        ]);
        $freq = [];
        foreach ($texts as $txt) {
            $words = preg_split('/\s+/', mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $txt ?? '')), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $w) {
                if (mb_strlen($w) >= 3 && !isset($stop[$w])) {
                    $freq[$w] = ($freq[$w] ?? 0) + 1;
                }
            }
        }
        arsort($freq);
        return array_slice($freq, 0, $limit, true);
    }
}