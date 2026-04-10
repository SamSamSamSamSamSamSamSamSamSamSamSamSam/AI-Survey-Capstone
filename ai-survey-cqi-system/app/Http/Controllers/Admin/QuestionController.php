<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(Survey $survey): View
    {
        return view('admin.surveys.questions.create', compact('survey'));
    }

    public function store(StoreQuestionRequest $request, Survey $survey): RedirectResponse
    {
        // Auto-assign order: last position + 1
        $nextOrder = $survey->questions()->max('order') + 1;

        $survey->questions()->create([
            'question_text' => $request->question_text,
            'category'      => $request->category,
            'type'          => $request->type,
            'order'         => $nextOrder,
        ]);

        return redirect()->route('admin.surveys.show', $survey->id)
                         ->with('success', 'Question added.');
    }

    public function edit(Survey $survey, Question $question): View
    {
        return view('admin.surveys.questions.edit', compact('survey', 'question'));
    }

    public function update(UpdateQuestionRequest $request, Survey $survey, Question $question): RedirectResponse
    {
        $question->update($request->validated());

        return redirect()->route('admin.surveys.show', $survey->id)
                         ->with('success', 'Question updated.');
    }

    public function destroy(Survey $survey, Question $question): RedirectResponse
    {
        $question->delete();

        // Re-sequence remaining questions
        $survey->questions()
               ->orderBy('order')
               ->get()
               ->each(fn ($q, $index) => $q->update(['order' => $index + 1]));

        return redirect()->route('admin.surveys.show', $survey->id)
                         ->with('success', 'Question removed.');
    }

    /**
     * Reorder questions via AJAX — accepts [{id, order}, ...] JSON payload.
     */
    public function reorder(Request $request, Survey $survey)
    {
        $request->validate([
            'order'          => ['required', 'array'],
            'order.*.id'     => ['required', 'integer', 'exists:questions,id'],
            'order.*.order'  => ['required', 'integer', 'min:1'],
        ]);

        foreach ($request->input('order') as $item) {
            Question::where('id', $item['id'])
                    ->where('survey_id', $survey->id) // scope to this survey only
                    ->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
