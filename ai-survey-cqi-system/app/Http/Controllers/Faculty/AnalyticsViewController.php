<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
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

        $hasData = FacultyAnalytics::where('faculty_id', $user->id)->exists();

        return view('faculty.analytics.charts', compact(
            'user',
            'activeSemester',
            'semesters',
            'hasData',
        ));
    }
}
