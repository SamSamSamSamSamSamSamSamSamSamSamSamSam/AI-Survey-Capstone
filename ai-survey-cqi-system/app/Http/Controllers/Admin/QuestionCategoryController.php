<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionCategoryController extends Controller
{
    public function index(): View
    {
        $categories = QuestionCategory::withCount(['surveyQuestions', 'templateQuestions'])
                                      ->orderBy('name')
                                      ->paginate(20);
        return view('admin.question-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.question-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:question_categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        QuestionCategory::create($request->only('name', 'description'));

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category created.');
    }

    public function edit(QuestionCategory $questionCategory): View
    {
        return view('admin.question-categories.edit', compact('questionCategory'));
    }

    public function update(Request $request, QuestionCategory $questionCategory): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', "unique:question_categories,name,{$questionCategory->id}"],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $questionCategory->update($request->only('name', 'description'));

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category updated.');
    }

    public function destroy(QuestionCategory $questionCategory): RedirectResponse
    {
        if ($questionCategory->surveyQuestions()->exists() || $questionCategory->templateQuestions()->exists()) {
            return back()->with('error', 'Cannot delete a category that is in use by existing questions.');
        }

        $questionCategory->delete();

        return redirect()->route('admin.question-categories.index')
                         ->with('success', 'Category deleted.');
    }
}
