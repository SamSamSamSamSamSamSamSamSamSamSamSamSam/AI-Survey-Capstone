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
        $surveyId   = $request->query('survey_id');
        $course     = $request->query('course');
        $semesterId = $request->query('semester_id');
        $cacheKey   = 'admin_dashboard_' . ($surveyId ?? 'all') . '_' . ($course ?? 'all') . '_' . ($semesterId ?? 'all');

        $allSurveys = Survey::select('id', 'title')->orderBy('created_at', 'desc')->get();
        $semesters  = Semester::orderByDesc('academic_year')->orderByDesc('semester_number')->get();

        $courses = Response::select('subject_id')
            ->distinct()
            ->with('subject:id,course_code')
            ->get()
            ->pluck('subject.course_code')
            ->filter()
            ->values();

        $data = Cache::remember($cacheKey, 60, function () use ($surveyId, $course, $semesterId) {
            $subjectId = null;
            if ($course) {
                $subjectId = Subject::where('course_code', $course)->value('id');
            }

            $stats           = $this->getDashboardStats($surveyId, $subjectId, $semesterId);
            $performanceData = $this->getFacultyPerformanceData($surveyId, $subjectId, $semesterId);
            $chartData       = $this->getMonthlyChartData($surveyId, $subjectId, $semesterId);
            $categoryScores  = $this->getCategoryScores($surveyId, $subjectId, $semesterId);

            return [
                ...$stats,
                ...$performanceData,
                ...$chartData,
                'categoryScores' => $categoryScores,
            ];
        });

        return view('admin.dashboard', array_merge($data, [
            'allSurveys'       => $allSurveys,
            'courses'          => $courses,
            'semesters'        => $semesters,
            'selectedSemester' => $semesterId,
        ]));
    }

    // ── KPI SUMMARY ────────────────────────────────────────────────────────────
    private function getDashboardStats($surveyId, $subjectId, $semesterId): array
    {
        $ratingValues = Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->when($surveyId,   fn($q) => $q->where('survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->pluck('response')
            ->map(fn($v) => is_numeric($v) ? (float)$v : null)
            ->filter();

        $ratingStats = $this->calculateRatingStats($ratingValues);

        $sentimentTotals = Response::select('sentiment_label', DB::raw('count(*) as cnt'))
            ->when($surveyId,   fn($q) => $q->where('survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->whereNotNull('sentiment_label')
            ->groupBy('sentiment_label')
            ->pluck('cnt', 'sentiment_label')
            ->toArray();

        $totalSent = array_sum($sentimentTotals);
        $overallPositivePct = $totalSent
            ? number_format((($sentimentTotals['positive'] ?? 0) / $totalSent) * 100, 1)
            : 'N/A';

        $distinctEvaluators = Response::when($surveyId,   fn($q) => $q->where('survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->distinct('evaluator_id')
            ->count('evaluator_id');

        $eligibleEvaluators = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();

        $participationPct = $eligibleEvaluators
            ? round($distinctEvaluators / max(1, $eligibleEvaluators) * 100, 1)
            : null;

        return [
            'mean'               => $ratingStats['mean'],
            'median'             => $ratingStats['median'],
            'mode'               => $ratingStats['mode'],
            'stddev'             => $ratingStats['stddev'],
            'rating_count'       => $ratingStats['count'],
            'sentimentTotals'    => $sentimentTotals,
            'overallPositivePct' => $overallPositivePct,
            'distinct_evaluators' => $distinctEvaluators,
            'eligible_evaluators' => $eligibleEvaluators,
            'participation_pct'  => $participationPct,
        ];
    }

    // ── TOP FACULTY + SENTIMENT ────────────────────────────────────────────────
    private function getFacultyPerformanceData($surveyId, $subjectId, $semesterId): array
    {
        $sentimentRows = Response::select('evaluatee_id', 'sentiment_label', DB::raw('count(*) as cnt'))
            ->when($surveyId,   fn($q) => $q->where('survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
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
            ->when($surveyId,   fn($q) => $q->where('responses.survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('responses.subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('responses.semester_id', $semesterId))
            ->groupBy('responses.evaluatee_id')
            ->having('cnt', '>=', 3)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        $evaluateeIds  = $sentimentRows->keys()->merge($topPerformersQuery->pluck('evaluatee_id'))->unique();
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
            $sentGroup  = $sentimentRows->get($row->evaluatee_id);
            $positivePct = 0;
            if ($sentGroup) {
                $total = $sentGroup->sum('cnt');
                $pos   = $sentGroup->first(fn($r) => $r->sentiment_label === 'positive')->cnt ?? 0;
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
    private function getCategoryScores($surveyId, $subjectId, $semesterId)
    {
        $rows = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select('questions.category', DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_score'))
            ->where('questions.type', 'rating')
            ->when($surveyId,   fn($q) => $q->where('responses.survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('responses.subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('responses.semester_id', $semesterId))
            ->groupBy('questions.category')
            ->get();

        return $rows->map(fn($r) => [
            'category' => $r->category ?? 'Uncategorized',
            'avg'      => round($r->avg_score, 2),
        ])->toArray();
    }

    // ── MONTHLY TRENDS ─────────────────────────────────────────────────────────
    private function getMonthlyChartData($surveyId, $subjectId, $semesterId): array
    {
        $monthlyRatings = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select(
                DB::raw("DATE_FORMAT(responses.created_at, '%Y-%m') as month"),
                DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating')
            )
            ->where('questions.type', 'rating')
            ->when($surveyId,   fn($q) => $q->where('responses.survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('responses.subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('responses.semester_id', $semesterId))
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
            ->when($surveyId,   fn($q) => $q->where('survey_id',   $surveyId))
            ->when($subjectId,  fn($q) => $q->where('subject_id',  $subjectId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
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

        $monthlyPosSeries  = array_map(fn($m) => $monthlyPositivePct[$m] ?? 0, $monthlyLabels);
        $formattedLabels   = array_map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'), $monthlyLabels);

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

        $mean   = round($ratingValues->avg(), 3);
        $sorted = $ratingValues->sort()->values();
        $mid    = (int) floor(($count - 1) / 2);
        $median = ($count % 2)
            ? $sorted[$mid]
            : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
        $mode     = $ratingValues->countBy()->sortDesc()->keys()->first();
        $variance = $ratingValues->reduce(fn($c, $x) => $c + pow($x - $mean, 2), 0) / $count;
        $stddev   = round(sqrt($variance), 3);

        return compact('count', 'mean', 'median', 'mode', 'stddev');
    }

    private function stopwords(): array
    {
        static $stopWords = null;
        if ($stopWords === null) {
            $list = [
                'the','and','for','with','this','that','from','have','were','their','they','them','will','your',
                'are','was','but','not','you','has','had','its','his','her','which','what','when','where','how',
                'our','also','can','could','should','would','there','been','about','than','then','each','into',
                'more','other','some','such','only','these','those','very','because','during','without','within',
                'instructor', 'teacher', 'faculty', 'professor',
            ];
            $stopWords = array_combine($list, $list);
        }
        return $stopWords;
    }
}