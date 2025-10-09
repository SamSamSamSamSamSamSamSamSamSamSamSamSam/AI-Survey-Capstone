<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\User;

class SurveyController extends Controller
{

    public function index()
    {
        $surveys = Survey::with(['questions', 'creator'])
                        ->withCount('questions')
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $faculty = User::whereHas('roles', function ($query) {
                        $query->whereIn('name', ['teacher', 'admin']);
                    })
                    ->orderBy('name')
                    ->get();

        return view('admin.surveys.create', compact('faculty'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'evaluatee_id' => 'required|exists:users,id', 
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|string',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'required|string|in:rating,text,multiple_choice',
            'target_role' => 'required|in:admin,teacher,student,both'
        ]);

   
        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'evaluatee_id' => $request->evaluatee_id,
            'created_by' => auth()->id(),
            'target_role' => $request->target_role,
            'is_active' => true
        ]);


        foreach ($request->questions as $index => $questionText) {
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => $questionText,
                'type' => $request->question_types[$index],
                'order' => $index
            ]);
        }

        return redirect()->route('admin.surveys.index')
                         ->with('success', 'Survey created successfully!');
    }


    public function destroy(Survey $survey)
    {
       
        $survey->questions()->delete();
        
 
        $survey->delete();

        return redirect()->route('admin.surveys.index')
                         ->with('success', 'Survey deleted successfully!');
    }

    public function toggleStatus(Survey $survey)
    {
        $survey->update([
            'is_active' => !$survey->is_active
        ]);

        $status = $survey->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
                         ->with('success', "Survey {$status} successfully!");
    }

    public function show($id)
    {

        $survey = Survey::with(['creator', 'questions', 'evaluatee'])->findOrFail($id);

        return view('admin.surveys.show', compact('survey'));
    }

    public function edit($id)
    {
        $survey = Survey::with('questions')->findOrFail($id);

     
        $evaluatees = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['teacher', 'admin']);
        })->get();

        return view('admin.surveys.edit', compact('survey', 'evaluatees'));
    }

    public function update(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);


        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_role' => 'required|string|in:admin,teacher,student,both',
            'evaluatee_id' => 'nullable|exists:users,id',
            'questions' => 'required|array',
            'questions.*' => 'required|string',
            'question_types' => 'required|array',
            'question_types.*' => 'in:rating,text',
        ]);


        $survey->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_role' => $validated['target_role'],
            'evaluatee_id' => $validated['evaluatee_id'] ?? null,
        ]);

        $survey->questions()->delete();

        foreach ($validated['questions'] as $index => $text) {
            $survey->questions()->create([
                'question_text' => $text,
                'type' => $validated['question_types'][$index],
            ]);
        }

        return redirect()
            ->route('admin.surveys.index', $survey->id)
            ->with('success', 'Survey updated successfully!');
    }


}
