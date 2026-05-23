<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\FacultyAnalytics;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnalyticsViewController extends Controller
{
    public function index(): View
    {
        $user           = Auth::user();
        $activeSemester = Semester::current();

        $semesters = Semester::whereHas('offerings.surveys.analytics', fn ($q) =>
            $q->where('faculty_id', $user->id)
        )
        ->orderByDesc('academic_start_year')
        ->orderByDesc('semester_number')
        ->get(['id', 'name', 'academic_start_year', 'semester_number', 'is_active']);

        // All courses this faculty member has analytics for, grouped by semester
        // Shape: [ semester_id => [ ['id'=>..., 'code'=>..., 'name'=>...], ... ], ... ]
        $coursesBySemester = CourseOffering::with('subject')
            ->whereHas('surveys.analytics', fn ($q) =>
                $q->where('faculty_id', $user->id)
            )
            ->where('teacher_id', $user->id)
            ->get()
            ->groupBy('semester_id')
            ->map(fn ($offerings) =>
                $offerings->map(fn ($o) => [
                    'id'   => $o->id,
                    'code' => $o->subject->course_code ?? 'Unknown',
                    'name' => $o->subject->name ?? '',
                ])->unique('code')->values()
            );

        $hasData = FacultyAnalytics::where('faculty_id', $user->id)->exists();

        return view('faculty.analytics.charts', compact(
            'user',
            'activeSemester',
            'semesters',
            'coursesBySemester',
            'hasData',
        ));
    }
}