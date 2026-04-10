<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use App\Models\Scale;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyQuestionController extends Controller
{
    public function create(Survey $survey): View
    {
        $categories = QuestionCategory::orderBy('name')->get();
        $scales     = Scale::with('options')->orderBy('name')->get();

        return view('admin.surveys.questions.create', compact('survey', 'categories', 'scales'));
    }

    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:rating,text'],
            'category_id'   => ['nullable', 'exists:question_categories,id'],
            'scale_id'      => ['nullable', 'exists:scales,id', 'required_if:question_type,rating'],
        ]);

        $next = $survey->questions()->max('order_number') + 1;

        $survey->questions()->create([
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'category_id'   => $request->category_id ?: null,
            'scale_id'      => $request->question_type === 'rating' ? $request->scale_id : null,
            'order_number'  => $next,
        ]);

        return redirect()->route('admin.surveys.show', $survey->id)
                         ->with('success', 'Question added.');
    }

    public function edit(Survey $survey, SurveyQuestion $question): View
    {
        $categories = QuestionCategory::orderBy('name')->get();
        $scales     = Scale::with('options')->orderBy('name')->get();

        return view('admin.surveys.questions.edit', compact('survey', 'question', 'categories', 'scales'));
    }

    public function update(Request $request, Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:rating,text'],
            'category_id'   => ['nullable', 'exists:question_categories,id'],
            'scale_id'      => ['nullable', 'exists:scales,id', 'required_if:question_type,rating'],
            'order_number'  => ['sometimes', 'integer', 'min:1'],
        ]);

        $question->update([
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'category_id'   => $request->category_id ?: null,
            'scale_id'      => $request->question_type === 'rating' ? $request->scale_id : null,
            'order_number'  => $request->input('order_number', $question->order_number),
        ]);

        return redirect()->route('admin.surveys.show', $survey->id)
                         ->with('success', 'Question updated.');
    }

    public function destroy(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $question->delete();

        $survey->questions()->orderBy('order_number')
               ->get()->each(fn ($q, $i) => $q->update(['order_number' => $i + 1]));

        return redirect()->route('admin.surveys.show', $survey->id)
                         ->with('success', 'Question removed.');
    }

    public function reorder(Request $request, Survey $survey)
    {
        $request->validate([
            'order'                  => ['required', 'array'],
            'order.*.id'             => ['required', 'integer'],
            'order.*.order_number'   => ['required', 'integer', 'min:1'],
        ]);

        foreach ($request->input('order') as $item) {
            SurveyQuestion::where('id', $item['id'])
                ->where('survey_id', $survey->id)
                ->update(['order_number' => $item['order_number']]);
        }

        return response()->json(['success' => true]);
    }
}
