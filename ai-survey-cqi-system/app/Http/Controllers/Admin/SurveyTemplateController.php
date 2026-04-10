<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use App\Models\Scale;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyTemplateController extends Controller
{
    public function index(): View
    {
        $templates = SurveyTemplate::withCount('questions')->latest()->paginate(15);
        return view('admin.survey-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.survey-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:survey_templates,name'],
            'description' => ['nullable', 'string'],
            'is_official' => ['boolean'],
            'is_active'   => ['boolean'],
        ]);

        $template = SurveyTemplate::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_official' => $request->boolean('is_official'),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.survey-templates.show', $template->id)
                         ->with('success', 'Template created. Now add questions.');
    }

    public function show(SurveyTemplate $surveyTemplate): View
    {
        $surveyTemplate->load(['questions.category', 'questions.scale']);
        $categories = QuestionCategory::orderBy('name')->get();
        $scales     = Scale::orderBy('name')->get();

        return view('admin.survey-templates.show', compact('surveyTemplate', 'categories', 'scales'));
    }

    public function edit(SurveyTemplate $surveyTemplate): View
    {
        return view('admin.survey-templates.edit', compact('surveyTemplate'));
    }

    public function update(Request $request, SurveyTemplate $surveyTemplate): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', "unique:survey_templates,name,{$surveyTemplate->id}"],
            'description' => ['nullable', 'string'],
            'is_official' => ['boolean'],
            'is_active'   => ['boolean'],
        ]);

        $surveyTemplate->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_official' => $request->boolean('is_official'),
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.survey-templates.show', $surveyTemplate->id)
                         ->with('success', 'Template updated.');
    }

    public function destroy(SurveyTemplate $surveyTemplate): RedirectResponse
    {
        if ($surveyTemplate->is_official) {
            return back()->with('error', 'The official university questionnaire cannot be deleted.');
        }

        $surveyTemplate->delete();

        return redirect()->route('admin.survey-templates.index')
                         ->with('success', 'Template deleted.');
    }

    // -------------------------------------------------------------------------
    // Template Question management
    // -------------------------------------------------------------------------

    public function storeQuestion(Request $request, SurveyTemplate $surveyTemplate): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:rating,text'],
            'category_id'   => ['nullable', 'exists:question_categories,id'],
            'scale_id'      => ['nullable', 'exists:scales,id'],
        ]);

        $next = $surveyTemplate->questions()->max('order_number') + 1;

        $surveyTemplate->questions()->create(array_merge($data, ['order_number' => $next]));

        return back()->with('success', 'Question added to template.');
    }

    public function updateQuestion(Request $request, SurveyTemplate $surveyTemplate, SurveyTemplateQuestion $question): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:rating,text'],
            'category_id'   => ['nullable', 'exists:question_categories,id'],
            'scale_id'      => ['nullable', 'exists:scales,id'],
            'order_number'  => ['required', 'integer', 'min:1'],
        ]);

        $question->update($data);

        return back()->with('success', 'Question updated.');
    }

    public function destroyQuestion(SurveyTemplate $surveyTemplate, SurveyTemplateQuestion $question): RedirectResponse
    {
        $question->delete();

        // Re-sequence
        $surveyTemplate->questions()->orderBy('order_number')
                        ->get()->each(fn ($q, $i) => $q->update(['order_number' => $i + 1]));

        return back()->with('success', 'Question removed.');
    }

    public function reorderQuestions(Request $request, SurveyTemplate $surveyTemplate)
    {
        $request->validate([
            'order'              => ['required', 'array'],
            'order.*.id'         => ['required', 'integer'],
            'order.*.order_number' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($request->input('order') as $item) {
            SurveyTemplateQuestion::where('id', $item['id'])
                ->where('survey_template_id', $surveyTemplate->id)
                ->update(['order_number' => $item['order_number']]);
        }

        return response()->json(['success' => true]);
    }
}
