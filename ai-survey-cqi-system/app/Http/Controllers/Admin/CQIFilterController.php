<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Survey;
use Illuminate\Http\Request;

class CQIFilterController extends Controller
{
    /**
     * Show the CQI report filter page.
     * Loads all semesters and, if a semester is selected, the surveys for that semester
     * (each survey = one faculty + one subject + one group combination).
     */
    public function index(Request $request)
    {
        $semesters = Semester::orderByDesc('academic_year')
            ->orderByDesc('semester_number')
            ->get();

        $surveys   = collect();
        $selectedSemesterId = $request->input('semester_id');

        if ($selectedSemesterId) {
            $surveys = Survey::with(['evaluatee', 'subject'])
                ->where('semester_id', $selectedSemesterId)
                ->whereNotNull('evaluatee_id')
                ->whereNotNull('subject_id')
                ->orderBy('group')
                ->get();
        }

        return view('admin.reports.filter', compact('semesters', 'surveys', 'selectedSemesterId'));
    }
}