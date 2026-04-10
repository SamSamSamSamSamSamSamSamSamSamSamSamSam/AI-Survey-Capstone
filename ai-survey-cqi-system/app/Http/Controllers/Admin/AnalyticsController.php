<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ComputeFacultyAnalyticsJob;
use App\Models\FacultyAnalytics;
use App\Models\Semester;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $semesters          = Semester::orderByDesc('academic_start_year')->orderByDesc('semester_number')->get();
        $activeSemester     = Semester::current();
        $selectedSemesterId = $request->input('semester_id', $activeSemester?->id);

        // Surveys that have analytics computed
        $query = FacultyAnalytics::with([
            'survey.offering.subject',
            'survey.offering.teacher',
            'survey.offering.semester',
            'survey.targetRole',
        ]);

        if ($selectedSemesterId) {
            $query->whereHas('survey.offering', fn ($q) =>
                $q->where('semester_id', $selectedSemesterId)
            );
        }

        $analytics = $query->latest('last_computed_at')->paginate(15)->withQueryString();

        return view('admin.analytics.index', compact('analytics', 'semesters', 'activeSemester', 'selectedSemesterId'));
    }

    public function show(FacultyAnalytics $analytic): View
    {
        $analytic->load([
            'survey.offering.subject',
            'survey.offering.teacher',
            'survey.offering.semester',
            'survey.questions.category',
            'faculty',
        ]);

        // Open-ended response samples
        $textResponses = \App\Models\Response::query()
            ->join('survey_attempts', 'responses.attempt_id', '=', 'survey_attempts.id')
            ->join('survey_questions', 'responses.survey_question_id', '=', 'survey_questions.id')
            ->where('survey_attempts.survey_id', $analytic->survey_id)
            ->whereNotNull('survey_attempts.submitted_at')
            ->where('survey_questions.question_type', 'text')
            ->whereNotNull('responses.text_response')
            ->with(['question', 'sentiment.sentimentType'])
            ->select('responses.*')
            ->get()
            ->groupBy('survey_question_id');

        // Check for existing CQI report
        $existingReport = \App\Models\CqiReport::where('survey_id', $analytic->survey_id)
                                                ->whereNull('deleted_at')
                                                ->latest()
                                                ->first();

        return view('admin.analytics.show', compact('analytic', 'textResponses', 'existingReport'));
    }

    /**
     * Manually trigger analytics recomputation.
     */
    public function recompute(Survey $survey): RedirectResponse
    {
        ComputeFacultyAnalyticsJob::dispatch($survey->id);

        return back()->with('success', 'Analytics recomputation queued.');
    }
}
