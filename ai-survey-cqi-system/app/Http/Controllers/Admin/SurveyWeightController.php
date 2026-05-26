<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\SurveyQuestion;
use App\Services\CategoryWeightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyWeightController extends Controller
{
    public function __construct(private CategoryWeightService $weightService) {}

    // ── Save weights for a survey template ───────────────────────────────────

    public function saveTemplateWeights(Request $request, SurveyTemplate $surveyTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'categories'   => ['required', 'array'],
            'categories.*' => ['required', 'integer', 'exists:question_categories,id'],
            'weights'      => ['required', 'array'],
            'weights.*'    => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $categories = $validated['categories'];
        $weights    = $validated['weights'];

        if (count($categories) !== count($weights)) {
            return back()->withErrors(['weights' => 'Category and weight counts do not match.']);
        }

        // Build map and validate total
        $map = array_combine($categories, array_map('floatval', $weights));
        $check = $this->weightService->validate($map);

        if (! $check['valid']) {
            return back()->withErrors(['weights' => $check['message']]);
        }

        // Persist — update all template questions for each category
        foreach ($map as $categoryId => $weight) {
            SurveyTemplateQuestion::where('survey_template_id', $surveyTemplate->id)
                ->where('category_id', $categoryId)
                ->where('question_type', 'rating')
                ->update(['category_weight' => $weight]);
        }

        return back()->with('success', 'Category weights saved successfully.');
    }

    // ── Save weights for a specific survey ───────────────────────────────────

    public function saveSurveyWeights(Request $request, Survey $survey): RedirectResponse
    {
        if ($survey->is_active) {
            return back()->withErrors(['weights' => 'Cannot modify weights on an active survey.']);
        }

        $validated = $request->validate([
            'categories'   => ['required', 'array'],
            'categories.*' => ['required', 'integer', 'exists:question_categories,id'],
            'weights'      => ['required', 'array'],
            'weights.*'    => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $categories = $validated['categories'];
        $weights    = $validated['weights'];

        if (count($categories) !== count($weights)) {
            return back()->withErrors(['weights' => 'Category and weight counts do not match.']);
        }

        $map   = array_combine($categories, array_map('floatval', $weights));
        $check = $this->weightService->validate($map);

        if (! $check['valid']) {
            return back()->withErrors(['weights' => $check['message']]);
        }

        foreach ($map as $categoryId => $weight) {
            SurveyQuestion::where('survey_id', $survey->id)
                ->where('category_id', $categoryId)
                ->where('question_type', 'rating')
                ->whereNull('deleted_at')
                ->update(['category_weight' => $weight]);
        }

        return back()->with('success', 'Survey category weights saved successfully.');
    }

    // ── Auto-redistribute weights for a template (JSON endpoint) ─────────────

    public function autoDistributeTemplate(SurveyTemplate $surveyTemplate): \Illuminate\Http\JsonResponse
    {
        $questions = SurveyTemplateQuestion::where('survey_template_id', $surveyTemplate->id)
            ->where('question_type', 'rating')
            ->whereNotNull('category_id')
            ->get();

        $weights = $this->weightService->distributeEqually($questions);

        // Persist
        foreach ($weights as $categoryId => $weight) {
            SurveyTemplateQuestion::where('survey_template_id', $surveyTemplate->id)
                ->where('category_id', $categoryId)
                ->where('question_type', 'rating')
                ->update(['category_weight' => $weight]);
        }

        return response()->json(['success' => true, 'weights' => $weights]);
    }

    // ── Auto-redistribute weights for a survey (JSON endpoint) ───────────────

    public function autoDistributeSurvey(Survey $survey): \Illuminate\Http\JsonResponse
    {
        if ($survey->is_active) {
            return response()->json(['success' => false, 'message' => 'Cannot modify active survey.'], 422);
        }

        $questions = SurveyQuestion::where('survey_id', $survey->id)
            ->where('question_type', 'rating')
            ->whereNotNull('category_id')
            ->whereNull('deleted_at')
            ->get();

        $weights = $this->weightService->distributeEqually($questions);

        foreach ($weights as $categoryId => $weight) {
            SurveyQuestion::where('survey_id', $survey->id)
                ->where('category_id', $categoryId)
                ->where('question_type', 'rating')
                ->whereNull('deleted_at')
                ->update(['category_weight' => $weight]);
        }

        return response()->json(['success' => true, 'weights' => $weights]);
    }
}
