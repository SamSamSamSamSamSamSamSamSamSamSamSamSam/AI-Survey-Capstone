<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyAnalytics;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;   
use Illuminate\View\View;

class AnalyticsViewController extends Controller
{
    public function index(Request $request): View   // ← $request injected
    {
        // Pass only the lightweight metadata — charts load via API
        $faculties = User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))
                         ->whereHas('analytics')
                         ->orderBy('name')
                         ->get(['id', 'name', 'user_id_number']);

        $semesters = Semester::orderByDesc('academic_start_year')
                             ->orderByDesc('semester_number')
                             ->get(['id', 'name', 'academic_start_year', 'semester_number', 'is_active']);

        // $request is now properly available
        $activeSemester = $request->has('semester_id')
            ? Semester::find($request->semester_id)
            : Semester::current();

        $hasData = $faculties->isNotEmpty();

        // Quick summary counts for the page header
        $totalAnalytics = FacultyAnalytics::count();
        $totalFaculty   = $faculties->count();

        return view('admin.analytics.charts', compact(
            'faculties',
            'semesters',
            'activeSemester',
            'totalAnalytics',
            'totalFaculty',
            'hasData',
        ));
    }
}