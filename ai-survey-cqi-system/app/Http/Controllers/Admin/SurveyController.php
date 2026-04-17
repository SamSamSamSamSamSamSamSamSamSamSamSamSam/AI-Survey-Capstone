<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $semesters          = Semester::orderByDesc('academic_start_year')->orderByDesc('semester_number')->get();
        $activeSemester     = Semester::current();
        $selectedSemesterId = $request->input('semester_id', $activeSemester?->id);

        $query = Survey::with(['offering.subject', 'offering.semester', 'offering.teacher', 'targetRole', 'creator', 'template'])
                       ->withCount('questions')
                       ->withTrashed();

        if ($selectedSemesterId) {
            $query->whereHas('offering', fn ($q) => $q->where('semester_id', $selectedSemesterId));
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('surveys.deleted_at');
        }

        $surveys = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('admin.surveys._table', compact('surveys'))->render();
        }

        return view('admin.surveys.index', compact('surveys', 'semesters', 'activeSemester', 'selectedSemesterId'));

    }

    public function create(): View
    {
        $activeSemester = Semester::current();

        $offerings = CourseOffering::with(['subject', 'teacher', 'semester'])
            ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id))
            ->whereNull('deleted_at')->get();

        $roles     = Role::orderBy('name')->get();
        $templates = SurveyTemplate::active()->orderByDesc('is_official')->orderBy('name')->get();

        return view('admin.surveys.create', compact('offerings', 'roles', 'templates', 'activeSemester'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Changed to expect an array of IDs
            'offering_id'    => ['required', 'array'], 
            'offering_id.*'  => ['exists:course_offerings,id'],
            'target_role_id' => ['required', 'exists:roles,id'],
            'template_id'    => ['nullable', 'exists:survey_templates,id'],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        DB::transaction(function () use ($request) {
            // Loop through each selected course offering
            foreach ($request->offering_id as $id) {
                $survey = Survey::create([
                    'offering_id'    => $id,
                    'created_by'     => Auth::id(),
                    'target_role_id' => $request->target_role_id,
                    'template_id'    => $request->template_id,
                    'title'          => $request->title,
                    'description'    => $request->description,
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                    'is_active'      => false,
                ]);

                // Auto-copy template questions if a template was selected
                if ($request->template_id) {
                    $template = SurveyTemplate::with('questions')->find($request->template_id);
                    $template?->copyQuestionsTo($survey);
                }
            }
        });

        $count = count($request->offering_id);
        $msg = "Successfully created {$count} surveys.";

        // Redirect to the index since we created multiple surveys
        return redirect()->route('admin.surveys.index')->with('success', $msg);
    }

    public function show(Survey $survey): View
    {
        $survey->load([
            'offering.subject', 'offering.semester', 'offering.teacher',
            'targetRole', 'creator', 'template',
            'questions.category', 'questions.scale.options',
        ]);

        $attemptCount   = $survey->attempts()->count();
        $submittedCount = $survey->attempts()->whereNotNull('submitted_at')->count();

        return view('admin.surveys.show', compact('survey', 'attemptCount', 'submittedCount'));
    }

    public function edit(Survey $survey): View
    {
        $offerings = CourseOffering::with(['subject', 'teacher', 'semester'])->whereNull('deleted_at')->get();
        $roles     = Role::orderBy('name')->get();
        $templates = SurveyTemplate::active()->orderByDesc('is_official')->orderBy('name')->get();

        return view('admin.surveys.edit', compact('survey', 'offerings', 'roles', 'templates'));
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        // BLOCK if active
        if ($survey->is_active) {
            return back()->with('error', 'Cannot modify survey while it is active.');
        }

        $request->validate([
            'offering_id'    => ['required', 'exists:course_offerings,id'],
            'target_role_id' => ['required', 'exists:roles,id'],
            'template_id'    => ['nullable', 'exists:survey_templates,id'],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $incomingTemplateId = $request->input('template_id');
        $templateChanged    = $incomingTemplateId && $incomingTemplateId != $survey->template_id;

        DB::transaction(function () use ($request, $survey, $incomingTemplateId, $templateChanged) {
            // Prepare the data
            $data = $request->only([
                'target_role_id', 'template_id', 'title', 
                'description', 'start_date', 'end_date'
            ]);

            // Fix: If offering_id is an array, take the first one
            $offeringId = $request->input('offering_id');
            $data['offering_id'] = is_array($offeringId) ? $offeringId[0] : $offeringId;

            $survey->update($data);

            // Replace questions only when the template actually changed
            if ($templateChanged) {
                $survey->questions()->delete();

                $template = SurveyTemplate::with('questions')->find($incomingTemplateId);
                $template?->copyQuestionsTo($survey);
            }
        });

        return redirect()->route('admin.surveys.show', $survey->id)->with('success', 'Survey updated.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();
        return redirect()->route('admin.surveys.index')->with('success', 'Survey archived.');
    }

    public function restore(string $id): RedirectResponse
    {
        Survey::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.surveys.index')->with('success', 'Survey restored.');
    }

    public function toggleActive(Survey $survey): RedirectResponse
    {
        if (! $survey->is_active && $survey->questions()->count() === 0) {
            return back()->with('error', 'Cannot activate a survey with no questions.');
        }

        $survey->update(['is_active' => ! $survey->is_active]);

        $survey->refresh();
        if (! $survey->is_active) {
            \App\Jobs\ComputeFacultyAnalyticsJob::dispatch($survey->id);
        }

        return back()->with('success', 'Survey ' . ($survey->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function attempts(Survey $survey): View
    {
        $survey->load(['offering.subject', 'offering.semester', 'targetRole']);

        $attempts = $survey->attempts()
            ->with(['respondent', 'responses.question.scale.options', 'responses.question.category'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.surveys.attempts', compact('survey', 'attempts'));
    }
}