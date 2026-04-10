<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyAnalytic;
use App\Models\Response;
use App\Models\ResponseSentiment;
use App\Models\User;
use App\Models\Survey;
use App\Models\Question;
use App\Models\SurveyAttempt;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ── Main dashboard ─────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $teacherId  = $request->query('teacher_id');
        $semesterId = $request->query('semester_id');
        $cacheKey   = 'admin_dashboard_' . ($teacherId ?? 'all') . '_' . ($semesterId ?? 'all');

        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->orderBy('name')
            ->get();

        $semesters = Semester::orderByDesc('academic_start_year')
            ->orderByDesc('semester_number')
            ->get();

        $data = Cache::remember($cacheKey, 60, function () use ($teacherId, $semesterId) {
            // ── "Big Picture" — sourced from FacultyAnalytic aggregates ─────────
            $kpis           = $this->getKpisFromAnalytics($teacherId, $semesterId);
            $topPerformers  = $this->getTopPerformersFromAnalytics($teacherId, $semesterId);

            // ── "Details" — sourced from raw response/sentiment tables ───────────
            $chartData      = $this->getMonthlyChartData($teacherId, $semesterId);
            $categoryScores = $this->getCategoryScores($teacherId, $semesterId);

            return array_merge($kpis, [
                'topPerformers'  => $topPerformers,
                'categoryScores' => $categoryScores,
                ...$chartData,
            ]);
        });

        return view('admin.dashboard', array_merge($data, [
            'teachers'         => $teachers,
            'semesters'        => $semesters,
            'selectedTeacher'  => $teacherId,
            'selectedSemester' => $semesterId,
        ]));
    }

    // ── KPIs from FacultyAnalytic ──────────────────────────────────────────────

    /**
     * Aggregate KPI summary from pre-computed faculty_analytics rows.
     * Filters by teacher (via offering.teacher_id) and/or semester (via offering.semester_id).
     */
    private function getKpisFromAnalytics($teacherId, $semesterId): array
    {
        $analytics = FacultyAnalytic::whereHas('offering', function ($q) use ($teacherId, $semesterId) {
            if ($teacherId)  $q->where('teacher_id', $teacherId);
            if ($semesterId) $q->where('semester_id', $semesterId);
        })->get();

        if ($analytics->isEmpty()) {
            return [
                'mean'               => null,
                'median'             => null,
                'mode'               => null,
                'stddev'             => null,
                'rating_count'       => 0,
                'overallPositivePct' => 'N/A',
                'participation_pct'  => null,
                'ratingDistribution' => array_values(array_fill(1, 5, 0)),
                'sentimentSummary'   => ['positive' => 0, 'neutral' => 0, 'negative' => 0],
            ];
        }

        // Weighted average rating across all offerings
        $totalResponses  = $analytics->sum('response_count');
        $weightedRating  = $analytics->sum(fn($a) => $a->avg_rating * $a->response_count);
        $mean            = $totalResponses ? round($weightedRating / $totalResponses, 3) : null;

        // Weighted sentiment percentages
        $weightedPos = $analytics->sum(fn($a) => $a->positive_sentiment_percent * $a->response_count);
        $weightedNeu = $analytics->sum(fn($a) => $a->neutral_sentiment_percent  * $a->response_count);
        $weightedNeg = $analytics->sum(fn($a) => $a->negative_sentiment_percent * $a->response_count);

        $positivePct = $totalResponses ? number_format($weightedPos / $totalResponses, 1) : 'N/A';

        // Rating distribution — raw query scoped to same offerings
        $offeringIds = $analytics->pluck('offering_id');
        $ratingDist  = $this->getRatingDistribution($offeringIds);

        // Median and mode from raw rating values
        $ratingValues   = $this->getRatingValues($offeringIds);
        $ratingStats    = $this->calculateRatingStats($ratingValues);

        // Participation: distinct students who submitted attempts / total enrolled students
        $distinctSubmitters = SurveyAttempt::whereHas('survey', fn($q) => $q->whereIn('offering_id', $offeringIds))
            ->whereNotNull('submitted_at')
            ->distinct('student_id')
            ->count('student_id');

        $eligibleStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $participationPct = $eligibleStudents ? round($distinctSubmitters / $eligibleStudents * 100, 1) : null;

        return [
            'mean'               => $mean ?? $ratingStats['mean'],
            'median'             => $ratingStats['median'],
            'mode'               => $ratingStats['mode'],
            'stddev'             => $ratingStats['stddev'],
            'rating_count'       => $totalResponses,
            'overallPositivePct' => $positivePct,
            'participation_pct'  => $participationPct,
            'ratingDistribution' => $ratingDist,
            'sentimentSummary'   => [
                'positive' => $totalResponses ? round($weightedPos / $totalResponses, 1) : 0,
                'neutral'  => $totalResponses ? round($weightedNeu / $totalResponses, 1) : 0,
                'negative' => $totalResponses ? round($weightedNeg / $totalResponses, 1) : 0,
            ],
        ];
    }

    // ── Top performers from FacultyAnalytic ────────────────────────────────────

    /**
     * Returns top-10 teachers ranked by avg_rating from pre-computed analytics.
     */
    private function getTopPerformersFromAnalytics($teacherId, $semesterId): array
    {
        $rows = FacultyAnalytic::with('offering.teacher')
            ->whereHas('offering', function ($q) use ($teacherId, $semesterId) {
                if ($teacherId)  $q->where('teacher_id', $teacherId);
                if ($semesterId) $q->where('semester_id', $semesterId);
            })
            ->where('response_count', '>=', 3)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        return $rows->map(fn($a) => [
            'teacher_id'   => $a->offering->teacher_id,
            'name'         => $a->offering->teacher?->name ?? 'Unknown',
            'avg_rating'   => round($a->avg_rating, 3),
            'count'        => $a->response_count,
            'positive_pct' => $a->positive_sentiment_percent,
        ])->toArray();
    }

    // ── Category scores (raw) ──────────────────────────────────────────────────

    /**
     * Average rating per question category.
     * Raw query — category breakdown is not stored in FacultyAnalytic.
     */
    private function getCategoryScores($teacherId, $semesterId): array
    {
        $rows = DB::table('responses')
            ->join('questions',       'responses.question_id', '=', 'questions.id')
            ->join('survey_attempts', 'responses.attempt_id',  '=', 'survey_attempts.id')
            ->join('surveys',         'survey_attempts.survey_id', '=', 'surveys.id')
            ->join('course_offerings','surveys.offering_id',   '=', 'course_offerings.id')
            ->select(
                'questions.category',
                DB::raw('avg(responses.rating_value) as avg_score')
            )
            ->where('questions.type', 'rating')
            ->whereNotNull('responses.rating_value')
            ->when($teacherId,  fn($q) => $q->where('course_offerings.teacher_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('course_offerings.semester_id', $semesterId))
            ->groupBy('questions.category')
            ->get();

        return $rows->map(fn($r) => [
            'category' => $r->category ?? 'Uncategorized',
            'avg'      => round($r->avg_score, 2),
        ])->toArray();
    }

    // ── Monthly trends (raw) ───────────────────────────────────────────────────

    /**
     * Monthly avg rating + positive sentiment % trend lines.
     * Raw query — time-series data is not stored in FacultyAnalytic.
     */
    private function getMonthlyChartData($teacherId, $semesterId): array
    {
        // Monthly average ratings
        $monthlyRatings = DB::table('responses')
            ->join('survey_attempts', 'responses.attempt_id',     '=', 'survey_attempts.id')
            ->join('surveys',         'survey_attempts.survey_id', '=', 'surveys.id')
            ->join('course_offerings','surveys.offering_id',       '=', 'course_offerings.id')
            ->join('questions',       'responses.question_id',     '=', 'questions.id')
            ->select(
                DB::raw("DATE_FORMAT(responses.created_at, '%Y-%m') as month"),
                DB::raw('avg(responses.rating_value) as avg_rating')
            )
            ->where('questions.type', 'rating')
            ->whereNotNull('responses.rating_value')
            ->when($teacherId,  fn($q) => $q->where('course_offerings.teacher_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('course_offerings.semester_id', $semesterId))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyLabels = $monthlyRatings->pluck('month')->toArray();
        $monthlyAvg    = $monthlyRatings->pluck('avg_rating')
            ->map(fn($v) => round($v, 3))
            ->toArray();

        // Monthly positive sentiment % via response_sentiments
        $monthlySent = DB::table('response_sentiments')
            ->join('responses',       'response_sentiments.response_id', '=', 'responses.id')
            ->join('survey_attempts', 'responses.attempt_id',            '=', 'survey_attempts.id')
            ->join('surveys',         'survey_attempts.survey_id',        '=', 'surveys.id')
            ->join('course_offerings','surveys.offering_id',              '=', 'course_offerings.id')
            ->join('sentiment_types', 'response_sentiments.sentiment_type_id', '=', 'sentiment_types.id')
            ->select(
                DB::raw("DATE_FORMAT(response_sentiments.created_at, '%Y-%m') as month"),
                'sentiment_types.label as sentiment_label',
                DB::raw('count(*) as cnt')
            )
            ->when($teacherId,  fn($q) => $q->where('course_offerings.teacher_id', $teacherId))
            ->when($semesterId, fn($q) => $q->where('course_offerings.semester_id', $semesterId))
            ->groupBy('month', 'sentiment_types.label')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $monthlyPositivePct = [];
        foreach ($monthlySent as $month => $group) {
            $total = $group->sum('cnt');
            $pos   = $group->firstWhere('sentiment_label', 'positive')->cnt ?? 0;
            $monthlyPositivePct[$month] = $total ? round($pos / $total * 100, 1) : 0;
        }

        $monthlyPosSeries = array_map(fn($m) => $monthlyPositivePct[$m] ?? 0, $monthlyLabels);
        $formattedLabels  = array_map(
            fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'),
            $monthlyLabels
        );

        return [
            'monthlyLabels'      => $formattedLabels,
            'monthlyAvg'         => $monthlyAvg,
            'monthlyPositivePct' => array_values($monthlyPosSeries),
        ];
    }

    // ── Other pages ────────────────────────────────────────────────────────────

    public function questionAnalysisList()
    {
        $surveys = Survey::with('offering.subject')
            ->select('id', 'title', 'created_at', 'offering_id')
            ->latest()
            ->get();

        return view('admin.analysis.surveys', compact('surveys'));
    }

    public function questionAnalysis(Request $request)
    {
        $surveyId = $request->query('survey_id');
        $qWord    = $request->query('q');
        $survey   = $surveyId ? Survey::with('questions')->find($surveyId) : null;

        $questions = $survey
            ? $survey->questions
            : Question::all();

        $stats = [];

        foreach ($questions as $question) {
            // Responses reached through attempt → question
            $responses = Response::where('question_id', $question->id)
                ->with('sentiments.sentimentType')
                ->get();

            $matched = $qWord && str_contains(strtolower($question->question_text), strtolower($qWord));

            if ($question->type === 'rating') {
                $vals = $responses->pluck('rating_value')->filter(fn($v) => is_numeric($v))->map(fn($v) => (float) $v);
                $dist = array_fill(1, 5, 0);
                foreach ($vals as $v) {
                    if (isset($dist[(int) $v])) $dist[(int) $v]++;
                }

                $stats[] = [
                    'question'     => $question,
                    'type'         => 'rating',
                    'count'        => $vals->count(),
                    'mean'         => $vals->count() ? round($vals->avg(), 2) : null,
                    'median'       => $vals->count()
                        ? $vals->sort()->values()[intdiv($vals->count() - 1, 2)]
                        : null,
                    'stddev'       => $vals->count() > 1
                        ? round(sqrt($vals->reduce(fn($c, $x) => $c + pow($x - $vals->avg(), 2), 0) / $vals->count()), 2)
                        : null,
                    'distribution' => $dist,
                    'matched'      => $matched,
                ];
            } else {
                $texts    = $responses->pluck('text_response')->filter();
                $topWords = $this->topWords($texts->all());

                $stats[] = [
                    'question'  => $question,
                    'type'      => 'text',
                    'count'     => $texts->count(),
                    'top_words' => $topWords,
                    'responses' => $responses->map(function ($r) {
                        // Primary sentiment: the one with the highest score
                        $topSentiment = $r->sentiments->sortByDesc('sentiment_score')->first();
                        return [
                            'created_at'      => $r->created_at?->toDateTimeString(),
                            'text_response'   => $r->text_response,
                            'sentiment_label' => $topSentiment?->sentimentType->label,
                            'sentiment_score' => $topSentiment?->sentiment_score,
                        ];
                    })->toArray(),
                    'matched' => $matched,
                ];
            }
        }

        return view('admin.analysis.questionAnalysis', compact('stats', 'survey', 'surveyId', 'qWord'));
    }

    public function wordCloud(Request $request)
    {
        $surveyId = $request->query('survey_id');

        $texts = Response::whereHas('question', fn($q) => $q->where('type', 'text'))
            ->when($surveyId, fn($q) => $q->whereHas(
                'attempt.survey', fn($q2) => $q2->where('id', $surveyId)
            ))
            ->pluck('text_response')
            ->filter();

        $words     = $this->topWords($texts->all(), 100);
        $wordLinks = [];

        foreach (array_keys($words) as $w) {
            $wordLinks[$w] = route('admin.analysis.questionAnalysis', [
                'survey_id' => $surveyId,
                'q'         => $w,
            ]);
        }

        return view('admin.analysis.wordCloud', compact('words', 'wordLinks', 'surveyId'));
    }

    public function evaluateeDetails(string $id)
    {
        $evaluatee = User::findOrFail($id);

        // All responses this teacher received, across all offerings they teach
        $responses = Response::with(['question', 'attempt.student', 'sentiments.sentimentType'])
            ->whereHas('attempt.survey.offering', fn($q) => $q->where('teacher_id', $id))
            ->latest()
            ->get()
            ->map(function ($r) {
                $topSentiment = $r->sentiments->sortByDesc('sentiment_score')->first();
                return [
                    'created_at'      => $r->created_at?->toDateTimeString(),
                    'question'        => $r->question?->question_text ?? 'N/A',
                    'rating_value'    => $r->rating_value,
                    'text_response'   => $r->text_response,
                    'sentiment_label' => $topSentiment?->sentimentType->label,
                    'sentiment_score' => $topSentiment?->sentiment_score,
                    'evaluator'       => $r->attempt->student?->name ?? 'Unknown',
                ];
            });

        $ratingVals = $responses->filter(fn($r) => is_numeric($r['rating_value']))->pluck('rating_value');
        $metrics    = [
            'count'  => $ratingVals->count(),
            'mean'   => $ratingVals->count() ? round($ratingVals->avg(), 2) : null,
            'median' => $ratingVals->count()
                ? $ratingVals->sort()->values()[intdiv($ratingVals->count() - 1, 2)]
                : null,
        ];

        return view('admin.evaluatee.evaluateeDetails', compact('evaluatee', 'responses', 'metrics'));
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Pull raw rating values for a set of offering IDs via the full chain:
     * responses → attempts → surveys → offerings
     */
    private function getRatingValues(Collection $offeringIds): Collection
    {
        return Response::whereHas('attempt.survey', fn($q) => $q->whereIn('offering_id', $offeringIds))
            ->whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->whereNotNull('rating_value')
            ->pluck('rating_value')
            ->map(fn($v) => (float) $v);
    }

    /**
     * Rating distribution [1..5] for a given set of offering IDs.
     */
    private function getRatingDistribution(Collection $offeringIds): array
    {
        $dist = array_fill(1, 5, 0);
        foreach ($this->getRatingValues($offeringIds) as $v) {
            $score = (int) round($v);
            if ($score >= 1 && $score <= 5) $dist[$score]++;
        }
        return array_values($dist);
    }

    private function calculateRatingStats(Collection $ratingValues): array
    {
        $count = $ratingValues->count();
        if ($count === 0) {
            return ['count' => 0, 'mean' => null, 'median' => null, 'mode' => null, 'stddev' => null];
        }

        $mean     = round($ratingValues->avg(), 3);
        $sorted   = $ratingValues->sort()->values();
        $mid      = (int) floor(($count - 1) / 2);
        $median   = ($count % 2) ? $sorted[$mid] : round(($sorted[$mid] + $sorted[$mid + 1]) / 2, 3);
        $mode     = $ratingValues->countBy()->sortDesc()->keys()->first();
        $variance = $ratingValues->reduce(fn($c, $x) => $c + pow($x - $mean, 2), 0) / $count;
        $stddev   = round(sqrt($variance), 3);

        return compact('count', 'mean', 'median', 'mode', 'stddev');
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
            $words = preg_split(
                '/\s+/',
                mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $txt ?? '')),
                -1,
                PREG_SPLIT_NO_EMPTY
            );
            foreach ($words as $w) {
                if (mb_strlen($w) >= 3 && ! isset($stop[$w])) {
                    $freq[$w] = ($freq[$w] ?? 0) + 1;
                }
            }
        }

        arsort($freq);
        return array_slice($freq, 0, $limit, true);
    }
}