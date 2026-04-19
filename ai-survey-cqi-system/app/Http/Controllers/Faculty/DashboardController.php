<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Survey\SurveyTakeController;
use App\Models\CourseOffering;
use App\Models\CqiReport;
use App\Models\Enrollment;
use App\Models\FacultyAnalytics;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user           = Auth::user();
        $activeSemester = Semester::current();

        // ── Active semester offerings taught ─────────────────────────────────
        $activeOfferings = CourseOffering::with(['subject', 'semester', 'block', 'offeringType'])
            ->where('teacher_id', $user->id)
            ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id))
            ->whereNull('deleted_at')
            ->withCount('enrollments')
            ->get();

        // ── Surveys targeting THIS faculty as respondent ─────────────────────
        // Uses shared method — correctly excludes own courses
        $surveyController = app(SurveyTakeController::class);
        $pendingSurveys   = $surveyController->getPendingSurveys($user);

        // Split active/inactive surveys for the courses this faculty TEACHES
        // (separate from pending — these are surveys on their own courses for analytics)
        $taughtSurveys = \App\Models\Survey::with(['offering.subject', 'targetRole'])
            ->whereHas('offering', fn ($q) =>
                $q->where('teacher_id', $user->id)
                  ->when($activeSemester, fn ($q2) => $q2->where('semester_id', $activeSemester->id))
            )
            ->whereNull('deleted_at')
            ->withCount(['attempts' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->get();

        $activeSurveys   = $taughtSurveys->where('is_active', true);
        $inactiveSurveys = $taughtSurveys->where('is_active', false);

        // ── Analytics summary ────────────────────────────────────────────────
        $analyticsRecords = FacultyAnalytics::with(['survey.offering.subject'])
            ->where('faculty_id', $user->id)
            ->when($activeSemester, fn ($q) =>
                $q->whereHas('survey.offering', fn ($q2) =>
                    $q2->where('semester_id', $activeSemester->id)
                )
            )
            ->get();

        $overallAvgRating = $analyticsRecords->whereNotNull('avg_rating')->avg('avg_rating');
        $totalResponses   = $analyticsRecords->sum('response_count');
        $avgPositivePct   = $analyticsRecords->whereNotNull('positive_sentiment_percent')->avg('positive_sentiment_percent');
        $avgNegativePct   = $analyticsRecords->whereNotNull('negative_sentiment_percent')->avg('negative_sentiment_percent');
        $avgNeutralPct    = $analyticsRecords->whereNotNull('neutral_sentiment_percent')->avg('neutral_sentiment_percent');

        $allCategoryScores = [];
        foreach ($analyticsRecords as $rec) {
            foreach ($rec->category_scores ?? [] as $cat => $score) {
                $allCategoryScores[$cat][] = $score;
            }
        }
        $avgCategoryScores = collect($allCategoryScores)
            ->map(fn ($scores) => round(array_sum($scores) / count($scores), 2))
            ->sortByDesc(fn ($v) => $v)
            ->toArray();

        // ── CQI Reports ──────────────────────────────────────────────────────
        $cqiReports = CqiReport::with(['survey.offering.subject', 'survey.offering.semester'])
            ->where('faculty_id', $user->id)
            ->whereNull('deleted_at')
            ->latest()
            ->take(5)
            ->get();

        // ── All-time totals ──────────────────────────────────────────────────
        $totalOfferings = CourseOffering::where('teacher_id', $user->id)
                                        ->whereNull('deleted_at')->count();

        $totalStudentsTaught = Enrollment::whereHas('offering', fn ($q) =>
            $q->where('teacher_id', $user->id)
        )->distinct('student_id')->count('student_id');

        return view('faculty.dashboard', compact(
            'user',
            'activeSemester',
            'activeOfferings',
            'pendingSurveys',        // surveys this faculty should respond to (peer eval)
            'activeSurveys',         // surveys on own courses (for analytics tracking)
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
