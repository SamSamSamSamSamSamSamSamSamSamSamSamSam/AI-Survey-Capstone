<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\CqiReport;
use App\Models\Enrollment;
use App\Models\FacultyAnalytics;
use App\Models\Semester;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user           = Auth::user();
        $activeSemester = Semester::current();

        // ── Active semester offerings taught by this faculty ─────────────────
        $activeOfferings = CourseOffering::with(['subject', 'semester', 'block', 'offeringType'])
            ->where('teacher_id', $user->id)
            ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id))
            ->whereNull('deleted_at')
            ->withCount('enrollments')
            ->get();

        // ── Surveys for this faculty's offerings ─────────────────────────────
        $surveys = Survey::with(['offering.subject', 'targetRole'])
            ->whereHas('offering', fn ($q) => $q->where('teacher_id', $user->id))
            ->when($activeSemester, fn ($q) =>
                $q->whereHas('offering', fn ($q2) => $q2->where('semester_id', $activeSemester->id))
            )
            ->whereNull('deleted_at')
            ->withCount(['attempts' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->get();

        $activeSurveys   = $surveys->where('is_active', true);
        $inactiveSurveys = $surveys->where('is_active', false);

        // ── Analytics summary across all surveys ─────────────────────────────
        $analyticsRecords = FacultyAnalytics::with(['survey.offering.subject'])
            ->where('faculty_id', $user->id)
            ->when($activeSemester, fn ($q) =>
                $q->whereHas('survey.offering', fn ($q2) => $q2->where('semester_id', $activeSemester->id))
            )
            ->get();

        // Aggregate stats
        $overallAvgRating   = $analyticsRecords->whereNotNull('avg_rating')->avg('avg_rating');
        $totalResponses     = $analyticsRecords->sum('response_count');
        $avgPositivePct     = $analyticsRecords->whereNotNull('positive_sentiment_percent')->avg('positive_sentiment_percent');
        $avgNegativePct     = $analyticsRecords->whereNotNull('negative_sentiment_percent')->avg('negative_sentiment_percent');
        $avgNeutralPct      = $analyticsRecords->whereNotNull('neutral_sentiment_percent')->avg('neutral_sentiment_percent');

        // Category scores — merge all analytics into averaged category scores
        $allCategoryScores = [];
        foreach ($analyticsRecords as $rec) {
            foreach ($rec->category_scores ?? [] as $cat => $score) {
                $allCategoryScores[$cat][] = $score;
            }
        }
        $avgCategoryScores = collect($allCategoryScores)
            ->map(fn ($scores) => round(array_sum(array_column($scores, 'score')) / count($scores), 2))
            ->sortByDesc(fn ($v) => $v)
            ->toArray();

        // ── CQI Reports ───────────────────────────────────────────────────────
        $cqiReports = CqiReport::with(['survey.offering.subject', 'survey.offering.semester'])
            ->where('faculty_id', $user->id)
            ->whereNull('deleted_at')
            ->latest()
            ->take(5)
            ->get();

        // ── All-time totals (for history section) ─────────────────────────────
        $totalOfferings = CourseOffering::where('teacher_id', $user->id)
                                        ->whereNull('deleted_at')->count();

        $totalStudentsTaught = Enrollment::whereHas('offering', fn ($q) =>
            $q->where('teacher_id', $user->id)
        )->distinct('student_id')->count('student_id');

        return view('faculty.dashboard', compact(
            'user',
            'activeSemester',
            'activeOfferings',
            'activeSurveys',
            'inactiveSurveys',
            'analyticsRecords',
            'overallAvgRating',
            'totalResponses',
            'avgPositivePct',
            'avgNegativePct',
            'avgNeutralPct',
            'avgCategoryScores',
            'cqiReports',
            'totalOfferings',
            'totalStudentsTaught',
        ));
    }
}
