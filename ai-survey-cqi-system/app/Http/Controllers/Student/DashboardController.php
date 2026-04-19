<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Semester;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user           = Auth::user();
        $activeSemester = Semester::current();

        // Determine which semester to show (default: active)
        $viewAll        = $request->boolean('all_semesters');
        $semesterScope  = $activeSemester && ! $viewAll ? $activeSemester : null;

        // ── Enrolled courses ─────────────────────────────────────────────────
        $enrollmentsQuery = Enrollment::with([
            'offering.subject',
            'offering.semester',
            'offering.teacher',
            'offering.block',
            'enrollmentType',
        ])->where('student_id', $user->id);

        if ($semesterScope) {
            $enrollmentsQuery->whereHas('offering', fn ($q) =>
                $q->where('semester_id', $semesterScope->id)
            );
        }

        $enrollments = $enrollmentsQuery->latest()->get();

        $surveyController = app(\App\Http\Controllers\Survey\SurveyTakeController::class);
        $pendingSurveys   = $surveyController->getPendingSurveys($user);

        // ── Pending surveys (live, targeted at student, not yet submitted) ───
        // $pendingSurveys = Survey::with(['offering.subject', 'offering.teacher', 'offering.semester'])
        //     ->whereHas('offering.enrollments', fn ($q) => $q->where('student_id', $user->id))
        //     ->whereHas('targetRole', fn ($q) => $q->where('name', 'student'))
        //     ->where('is_active', true)
        //     ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
        //     ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
        //     ->whereDoesntHave('attempts', fn ($q) =>
        //         $q->where('student_id', $user->id)->whereNotNull('submitted_at')
        //     )
        //     ->whereNull('deleted_at')
        //     ->withCount('questions')
        //     ->get();

        $completedAttempts = SurveyAttempt::with([
                'survey' => function($query) {
                    $query->withTrashed(); // This allows the title to load even if archived
                }, 
                'survey.offering.subject', 
                'survey.offering.semester'
            ])
            ->where('student_id', $user->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->take(10)
            ->get();

        // ── Stats ─────────────────────────────────────────────────────────────
        $totalEnrolled    = Enrollment::where('student_id', $user->id)->count();
        $totalCompleted   = SurveyAttempt::where('student_id', $user->id)->whereNotNull('submitted_at')->count();
        $pendingCount     = $pendingSurveys->count();

        // Active semester enrollment count
        $activeSemEnrolled = $activeSemester
            ? Enrollment::where('student_id', $user->id)
                         ->whereHas('offering', fn ($q) => $q->where('semester_id', $activeSemester->id))
                         ->count()
            : 0;

        return view('student.dashboard', compact(
            'user',
            'activeSemester',
            'viewAll',
            'enrollments',
            'pendingSurveys',
            'completedAttempts',
            'totalEnrolled',
            'totalCompleted',
            'pendingCount',
            'activeSemEnrolled',
        ));
    }
}
