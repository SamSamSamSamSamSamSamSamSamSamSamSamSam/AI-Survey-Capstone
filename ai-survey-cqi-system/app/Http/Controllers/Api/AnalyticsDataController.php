<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacultyAnalytics;
use App\Models\Response as SurveyResponse;
use App\Models\Semester;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsDataController extends Controller
{
    // -------------------------------------------------------------------------
    // Shared: resolve which faculty IDs to scope to
    // Admin: can pass ?faculty_id=X or gets all
    // Faculty: always scoped to self
    // -------------------------------------------------------------------------

    private function resolveFacultyScope(Request $request): array|null
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            if ($fid = $request->input('faculty_id')) {
                return [$fid];
            }
            return null; // null = all faculty
        }

        return [$user->id]; // faculty sees only own data
    }

    private function baseQuery(Request $request)
    {
        $scope = $this->resolveFacultyScope($request);

        $q = FacultyAnalytics::with([
            'survey.offering.subject',
            'survey.offering.semester',
            'survey.offering.teacher',
        ]);

        if ($scope) {
            $q->whereIn('faculty_id', $scope);
        }

        // Semester filter
        if ($semId = $request->input('semester_id')) {
            $q->whereHas('survey.offering', fn ($sq) =>
                $sq->where('semester_id', $semId)
            );
        }

        return $q;
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/meta
    // Returns available semesters, courses, faculty for filter dropdowns
    // -------------------------------------------------------------------------

    public function meta(Request $request): JsonResponse
    {
        $scope = $this->resolveFacultyScope($request);

        $semesters = Semester::orderByDesc('academic_start_year')
                             ->orderByDesc('semester_number')
                             ->get(['id', 'name', 'academic_start_year', 'semester_number', 'is_active'])
                             ->map(fn ($s) => [
                                 'id'       => $s->id,
                                 'label'    => $s->full_label,
                                 'is_active'=> $s->is_active,
                             ]);

        $facultyQuery = User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))
                            ->whereHas('analytics'); // only faculty with analytics

        if ($scope) {
            $facultyQuery->whereIn('id', $scope);
        }

        $faculty = $facultyQuery->orderBy('name')
                                ->get(['id', 'name', 'user_id_number']);

        return response()->json([
            'semesters' => $semesters,
            'faculty'   => $faculty,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/overview
    // Summary stats + distribution + sentiment + category scores
    // -------------------------------------------------------------------------

    public function overview(Request $request): JsonResponse
    {
        $records = $this->baseQuery($request)->get();

        if ($records->isEmpty()) {
            return response()->json(['empty' => true]);
        }

        // ── Summary stats ─────────────────────────────────────────────────────
        $totalResponses   = $records->sum('response_count');
        $avgRating        = $records->whereNotNull('avg_rating')->avg('avg_rating');
        $avgPositive      = $records->whereNotNull('positive_sentiment_percent')->avg('positive_sentiment_percent');
        $avgNeutral       = $records->whereNotNull('neutral_sentiment_percent')->avg('neutral_sentiment_percent');
        $avgNegative      = $records->whereNotNull('negative_sentiment_percent')->avg('negative_sentiment_percent');

        // ── Rating distribution (count of records by rounded avg_rating) ──────
        $distribution = $records->whereNotNull('avg_rating')
            ->groupBy(fn ($r) => (string) round($r->avg_rating))
            ->map->count()
            ->toArray();

        $dist = [];
        for ($i = 1; $i <= 5; $i++) {
            $dist[(string) $i] = $distribution[(string) $i] ?? 0;
        }

        // ── Category scores ───────────────────────────────────────────────────
        $allCatScores = [];
        foreach ($records as $rec) {
            foreach ($rec->category_scores ?? [] as $cat => $score) {
                $allCatScores[$cat][] = is_array($score) ? array_shift($score) : $score;
            }
        }
        $avgCatScores = collect($allCatScores)
            ->map(fn ($scores) => round(collect($scores)->avg(), 2)) // Use Laravel's avg()
            ->sortByDesc(fn ($v) => $v)
            ->toArray();

        return response()->json([
            'summary' => [
                'total_responses'  => $totalResponses,
                'avg_rating'       => round($avgRating, 2),
                'avg_positive_pct' => round($avgPositive, 1),
                'avg_neutral_pct'  => round($avgNeutral, 1),
                'avg_negative_pct' => round($avgNegative, 1),
                'surveys_count'    => $records->count(),
            ],
            'distribution'   => $dist,
            'category_scores'=> $avgCatScores,
            'sentiment'      => [
                'positive' => round($avgPositive, 1),
                'neutral'  => round($avgNeutral, 1),
                'negative' => round($avgNegative, 1),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/trends
    // Per-semester aggregated data for trend charts
    // -------------------------------------------------------------------------

    public function trends(Request $request): JsonResponse
    {
        $scope   = $this->resolveFacultyScope($request);
        $metric  = $request->input('metric', 'avg_rating');

        $allowed = ['avg_rating', 'positive_sentiment_percent', 'negative_sentiment_percent', 'response_count'];
        if (! in_array($metric, $allowed)) {
            $metric = 'avg_rating';
        }

        $semesters = Semester::orderBy('academic_start_year')
                             ->orderBy('semester_number')
                             ->get();

        $semesterData = [];
        $courseData   = [];

        foreach ($semesters as $sem) {
            $q = FacultyAnalytics::whereHas('survey.offering', fn ($sq) =>
                $sq->where('semester_id', $sem->id)
            );

            if ($scope) {
                $q->whereIn('faculty_id', $scope);
            }

            $records = $q->with('survey.offering.subject')->get();

            if ($records->isEmpty()) continue;

            $val = $metric === 'response_count'
                ? $records->sum('response_count')
                : round($records->whereNotNull($metric)->avg($metric), 2);

            $semesterData[] = [
                'semester_id'    => $sem->id,
                'semester_label' => $sem->full_label,
                'value'          => $val ?? 0,
            ];

            // Per-course breakdown for this semester
            foreach ($records as $rec) {
                $code = $rec->survey->offering->subject->course_code ?? 'Unknown';
                if (! isset($courseData[$code])) {
                    $courseData[$code] = [];
                }
                $courseData[$code][$sem->full_label] = round($rec->$metric ?? 0, 2);
            }
        }

        return response()->json([
            'semester_series' => $semesterData,
            'course_series'   => $courseData,
            'metric'          => $metric,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/categories
    // Category scores per semester + over-time breakdown
    // -------------------------------------------------------------------------

    public function categories(Request $request): JsonResponse
    {
        $scope = $this->resolveFacultyScope($request);

        $semesters = Semester::orderBy('academic_start_year')
                             ->orderBy('semester_number')
                             ->get();

        $overTime  = [];
        $latest    = null;

        foreach ($semesters as $sem) {
            $q = FacultyAnalytics::whereHas('survey.offering', fn ($sq) =>
                $sq->where('semester_id', $sem->id)
            );

            if ($scope) {
                $q->whereIn('faculty_id', $scope);
            }

            $records = $q->get();
            if ($records->isEmpty()) continue;

            $allCats = [];
            foreach ($records as $rec) {
                foreach ($rec->category_scores ?? [] as $cat => $score) {
                    $allCats[$cat][] = $score;
                }
            }

            $avgCats = collect($allCats)
                ->map(function ($scores) {
                    return round(collect($scores)->flatten()->filter(fn($v) => is_numeric($v))->avg(), 2);
                })
                ->toArray();

            $overTime[$sem->full_label] = $avgCats;
            $latest = $avgCats;
        }

        // Department avg per category (aggregated across ALL faculty, all semesters)
        $deptQuery = FacultyAnalytics::all();
        $deptCats  = [];
        foreach ($deptQuery as $rec) {
            foreach ($rec->category_scores ?? [] as $cat => $score) {
                $deptCats[$cat][] = $score;
            }
        }
        $deptAvgCats = collect($deptCats)
            ->map(function ($s) {
            return round(collect($s)->flatten()->filter(fn($v) => is_numeric($v))->avg(), 2);
        })
            ->toArray();

        return response()->json([
            'latest_scores'   => $latest ?? [],
            'over_time'       => $overTime,
            'dept_avg'        => $deptAvgCats,
            'passing_threshold'=> (float) setting('survey.passing_threshold', 3.0),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/sentiment
    // Sentiment over time + per course + keywords
    // -------------------------------------------------------------------------

    public function sentiment(Request $request): JsonResponse
    {
        $scope = $this->resolveFacultyScope($request);

        $semesters = Semester::orderBy('academic_start_year')
                             ->orderBy('semester_number')
                             ->get();

        $trend        = [];
        $byCourse     = [];
        $allKeywords  = [];

        foreach ($semesters as $sem) {
            $q = FacultyAnalytics::whereHas('survey.offering', fn ($sq) =>
                $sq->where('semester_id', $sem->id)
            );

            if ($scope) $q->whereIn('faculty_id', $scope);

            $records = $q->with('survey.offering.subject')->get();
            if ($records->isEmpty()) continue;

            $trend[$sem->full_label] = [
                'positive' => round($records->avg('positive_sentiment_percent'), 1),
                'neutral'  => round($records->avg('neutral_sentiment_percent'), 1),
                'negative' => round($records->avg('negative_sentiment_percent'), 1),
            ];

            // Per-course for latest semester
            if ($sem->id === $semesters->last()->id) {
                foreach ($records as $rec) {
                    $code = $rec->survey->offering->subject->course_code ?? 'Unknown';
                    $byCourse[$code] = [
                        'positive' => round($rec->positive_sentiment_percent ?? 0, 1),
                        'neutral'  => round($rec->neutral_sentiment_percent ?? 0, 1),
                        'negative' => round($rec->negative_sentiment_percent ?? 0, 1),
                    ];
                }
            }

            foreach ($records as $rec) {
                foreach ($rec->top_keywords ?? [] as $kw) {
                    $allKeywords[$kw] = ($allKeywords[$kw] ?? 0) + 1;
                }
            }
        }

        arsort($allKeywords);
        $topKeywords = array_slice($allKeywords, 0, 20, true);

        return response()->json([
            'trend'       => $trend,
            'by_course'   => $byCourse,
            'keywords'    => $topKeywords,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/benchmark
    // Faculty vs department avg, vs historical self, faculty ranking
    // -------------------------------------------------------------------------

    public function benchmark(Request $request): JsonResponse
    {
        $scope     = $this->resolveFacultyScope($request);
        $semId     = $request->input('semester_id');
        $against   = $request->input('against', 'dept'); // dept | history | sem

        // My records
        $myQuery = FacultyAnalytics::query();
        if ($scope) $myQuery->whereIn('faculty_id', $scope);
        if ($semId) $myQuery->whereHas('survey.offering', fn ($q) => $q->where('semester_id', $semId));

        $myRecords = $myQuery->with(['survey.offering.semester', 'survey.offering.subject'])->get();

        // All-faculty records (for dept benchmark + ranking)
        $allQuery = FacultyAnalytics::query();
        if ($semId) $allQuery->whereHas('survey.offering', fn ($q) => $q->where('semester_id', $semId));
        $allRecords = $allQuery->with(['faculty', 'survey.offering.subject'])->get();

        // Per-semester my avg
        $mySemData = $myRecords->groupBy(
            fn ($r) => $r->survey->offering->semester->full_label
        )->map(fn ($recs) => round($recs->avg('avg_rating'), 2));

        // Dept avg per semester
        $deptSemData = $allRecords->groupBy(
            fn ($r) => $r->survey->offering->semester->full_label
        )->map(fn ($recs) => round($recs->avg('avg_rating'), 2));

        // Benchmark values based on type
        $benchmarkSeries = match ($against) {
            'history' => $mySemData->mapWithKeys(fn ($v, $k) => [$k => round($myRecords->avg('avg_rating'), 2)]),
            default   => $deptSemData,
        };

        // Category comparison
        $myLatestCats = [];
        $deptCats     = [];

        foreach ($myRecords as $rec) {
            foreach ($rec->category_scores ?? [] as $cat => $score) {
                $myLatestCats[$cat][] = $score;
            }
        }
        foreach ($allRecords as $rec) {
            foreach ($rec->category_scores ?? [] as $cat => $score) {
                $deptCats[$cat][] = $score;
            }
        }

        $myCatAvg = collect($myLatestCats)
            ->map(fn ($s) => round(collect($s)->flatten()->filter(fn($v) => is_numeric($v))->avg(), 2))
            ->toArray();

        $deptCatAvg = collect($deptCats)
            ->map(fn ($s) => round(collect($s)->flatten()->filter(fn($v) => is_numeric($v))->avg(), 2))
            ->toArray();

        // Faculty ranking
        $ranking = $allRecords->groupBy('faculty_id')
            ->map(fn ($recs) => [
                'faculty_id'   => $recs->first()->faculty_id,
                'faculty_name' => $recs->first()->faculty?->name ?? 'Unknown',
                'avg_rating'   => round($recs->avg('avg_rating'), 2),
                'response_count'=> $recs->sum('response_count'),
            ])
            ->sortByDesc('avg_rating')
            ->values()
            ->toArray();

        $myFacultyIds = $scope ?? [];
        foreach ($ranking as &$r) {
            $r['is_me'] = in_array($r['faculty_id'], $myFacultyIds);
        }

        return response()->json([
            'my_series'        => $mySemData,
            'benchmark_series' => $benchmarkSeries,
            'benchmark_label'  => match ($against) {
                'dept'    => 'Department average',
                'sem'     => 'Semester average',
                'history' => 'Your historical average',
                default   => 'Benchmark',
            },
            'my_category_avg'   => $myCatAvg,
            'dept_category_avg' => $deptCatAvg,
            'ranking'           => $ranking,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/analytics/pivot
    // Flexible pivot — x, y, group_by parameters
    // -------------------------------------------------------------------------

    public function pivot(Request $request): JsonResponse
    {
        $scope   = $this->resolveFacultyScope($request);
        $xAxis   = $request->input('x', 'semester');   // semester | course | category
        $yMetric = $request->input('y', 'avg_rating'); // avg_rating | response_count | positive_pct | negative_pct
        $groupBy = $request->input('group', 'none');   // none | course | semester

        $allowed_y = ['avg_rating', 'response_count', 'positive_sentiment_percent', 'negative_sentiment_percent'];
        if (! in_array($yMetric, $allowed_y)) {
            $yMetric = 'avg_rating';
        }

        $records = FacultyAnalytics::with([
            'survey.offering.subject',
            'survey.offering.semester',
        ]);

        if ($scope) $records->whereIn('faculty_id', $scope);
        $records = $records->get();

        // Build rows based on x-axis dimension
        $result = [];

        if ($xAxis === 'semester') {
            $grouped = $records->groupBy(fn ($r) => $r->survey->offering->semester->full_label ?? 'Unknown');
        } elseif ($xAxis === 'course') {
            $grouped = $records->groupBy(fn ($r) => $r->survey->offering->subject->course_code ?? 'Unknown');
        } else { // category
            // Flatten category scores
            $catData = [];
            foreach ($records as $rec) {
                foreach ($rec->category_scores ?? [] as $cat => $score) {
                    $catData[$cat][] = $score;
                }
            }
            $result = collect($catData)
                ->map(function ($scores, $categoryName) {
                    $avg = collect($scores)
                        ->flatten()
                        ->filter(fn($v) => is_numeric($v))
                        ->avg();

                    return [
                        'x'     => $categoryName, // Use the actual category key here
                        'value' => round($avg ?? 0, 2)
                    ];
                })
                ->values() // Optional: resets keys to 0, 1, 2... for clean JSON arrays
                ->toArray();

            $rows = [];
            foreach ($catData as $cat => $scores) {
                $numericScores = collect($scores)->flatten()->filter(fn($v) => is_numeric($v));
                $average = $numericScores->avg() ?? 0;
                $rows[] = [
                    'label' => $cat, 
                    'value' => round($average, 2), 
                    'group' => null
                ];
            }

            return response()->json(['rows' => $rows, 'metric' => $yMetric, 'x_axis' => $xAxis, 'group_by' => $groupBy]);
        }

        if ($groupBy === 'none') {
            $rows = $grouped->map(function ($recs, $label) use ($yMetric) {
                $value = $yMetric === 'response_count'
                    ? $recs->sum('response_count')
                    : round($recs->whereNotNull($yMetric)->avg($yMetric) ?? 0, 2);
                return ['label' => $label, 'value' => $value, 'group' => null];
            })->values()->toArray();
        } else {
            // Group within each x-axis value
            $subKey = $groupBy === 'course'
                ? fn ($r) => $r->survey->offering->subject->course_code ?? 'Unknown'
                : fn ($r) => $r->survey->offering->semester->full_label ?? 'Unknown';

            $rows = [];
            foreach ($grouped as $label => $recs) {
                foreach ($recs->groupBy($subKey) as $grp => $subRecs) {
                    $value = $yMetric === 'response_count'
                        ? $subRecs->sum('response_count')
                        : round($subRecs->whereNotNull($yMetric)->avg($yMetric) ?? 0, 2);
                    $rows[] = ['label' => $label, 'value' => $value, 'group' => $grp];
                }
            }
        }

        return response()->json([
            'rows'    => $rows,
            'metric'  => $yMetric,
            'x_axis'  => $xAxis,
            'group_by'=> $groupBy,
        ]);
    }
}
