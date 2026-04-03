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
        $semesters          = Semester::orderByDesc('academic_year')->orderByDesc('semester_number')->get();
        $selectedSemesterId = $request->query('semester_id');
        $hasApiKey          = Setting::hasApiKey();

        $surveys = collect();

        if ($selectedSemesterId) {
            $surveys = Survey::with(['evaluatee', 'subject', 'semester'])
                ->where('semester_id', $selectedSemesterId)
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