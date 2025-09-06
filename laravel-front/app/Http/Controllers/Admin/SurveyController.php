<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Question;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::with(['questions', 'creator'])
                        ->withCount('questions') // ADDED THIS LINE
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        return view('admin.surveys.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|string',
            'target_role' => 'required|in:admin,teacher,student,both'
        ]);

        // Create the survey
        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'target_role' => $request->target_role,
            'is_active' => true
        ]);

        // Add questions
        foreach ($request->questions as $index => $questionText) {
            // Determine question type based on content or separate field
            $type = 'text'; // Default type
            if (strpos(strtolower($questionText), 'scale') !== false || 
                strpos(strtolower($questionText), 'rate') !== false) {
                $type = 'rating';
            }
            
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => $questionText,
                'type' => $type,
                'order' => $index
            ]);
        }

        return redirect()->route('admin.surveys.index')
                         ->with('success', 'Survey created successfully!');
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions', 'creator', 'responses']);
        return view('admin.surveys.show', compact('survey'));
    }

    public function edit(Survey $survey)
    {
        $survey->load('questions');
        return view('admin.surveys.edit', compact('survey'));
    }

    public function update(Request $request, Survey $survey)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|string',
            'target_role' => 'required|in:admin,teacher,student,both'
        ]);

        // Update the survey
        $survey->update([
            'title' => $request->title,
            'description' => $request->description,
            'target_role' => $request->target_role
        ]);

        // Remove existing questions
        $survey->questions()->delete();

        // Add updated questions
        foreach ($request->questions as $index => $questionText) {
            $type = 'text';
            if (strpos(strtolower($questionText), 'scale') !== false || 
                strpos(strtolower($questionText), 'rate') !== false) {
                $type = 'rating';
            }
            
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => $questionText,
                'type' => $type,
                'order' => $index
            ]);
        }

        return redirect()->route('admin.surveys.index')
                         ->with('success', 'Survey updated successfully!');
    }

    public function destroy(Survey $survey)
    {
        // Delete related questions first
        $survey->questions()->delete();
        
        // Then delete the survey
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
}