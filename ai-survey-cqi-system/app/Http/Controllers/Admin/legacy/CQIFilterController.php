<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Survey;
use App\Models\Setting;
use Illuminate\Http\Request;

class CQIFilterController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::orderByDesc('academic_start_year')
            ->orderByDesc('semester_number')
            ->get();

        $selectedSemesterId = $request->query('semester_id');
        $hasApiKey          = Setting::hasApiKey();
        $surveys            = collect();

        if ($selectedSemesterId) {
            // Surveys are linked to offerings, offerings are linked to semesters.
            // Eager-load offering → subject + teacher for the filter table display.
            $surveys = Survey::with(['offering.subject', 'offering.teacher', 'targetRole'])
                ->whereHas('offering', fn($q) => $q->where('semester_id', $selectedSemesterId))
                ->latest()
                ->get();
        }

        return view('admin.reports.filter', compact(
            'semesters',
            'selectedSemesterId',
            'surveys',
            'hasApiKey'
        ));
    }
}