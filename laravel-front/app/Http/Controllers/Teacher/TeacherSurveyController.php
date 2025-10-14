<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Response;
use App\Jobs\RunSentimentAnalysis;
use Illuminate\Http\Request;

class TeacherSurveyController extends Controller
{
    public function index()
    {
        $survey = Survey::where('is_active', true)
                        ->whereIn('target_role', ['teacher', 'both'])
                        ->orderBy('created_at', 'desc')
                        ->get();
        if (!$survey) {
            // Handle case where no active survey is found
            return redirect()->route('teacher.dashboard')->with('error', 'No active survey found.');
        }

        return view('teacher.survey', compact('survey'));
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions', 'evaluatee']);

        // Check if this teacher (evaluator) already answered
        $alreadySubmitted = \App\Models\Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        return view('teacher.survey_take', compact('survey', 'alreadySubmitted'));
    }



   public function submit(Request $request, Survey $survey)
    {
        // Prevent double submission
        $alreadySubmitted = Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->route('teacher.survey')
                ->with('error', 'You have already submitted this survey.');
        }

        $request->validate([
            'responses' => 'required|array',
        ]);

        $evaluateeId = $request->input('evaluatee_id', $survey->evaluatee_id);
        $subjectId = $request->input('subject_id');

        foreach ($request->responses as $questionId => $answer) {
            Response::updateOrCreate(
                [
                    'survey_id' => $survey->id,
                    'question_id' => $questionId,
                    'evaluator_id' => auth()->id(),
                    'evaluatee_id' => $evaluateeId,
                    'subject_id' => $subjectId,
                ],
                [
                    'response' => $answer,
                ]
            );
        }
        
        RunSentimentAnalysis::dispatch();

        return redirect()->route('teacher.survey')
            ->with('success', 'Survey submitted successfully!');
    }

}
