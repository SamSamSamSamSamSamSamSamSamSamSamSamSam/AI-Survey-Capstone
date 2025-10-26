<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Response;
use App\Jobs\RunSentimentAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSurveyController extends Controller
{
    public function index()
    {
        $survey = Survey::where('is_active', true)
                        ->whereIn('target_role', ['teacher', 'both'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('teacher.survey', compact('survey'));
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions', 'evaluatee']);

        $alreadySubmitted = Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        return view('teacher.survey_take', compact('survey', 'alreadySubmitted'));
    }

    public function submit(Request $request, Survey $survey)
    {
        $student = auth()->user();

        // Prevent double submission
        $alreadySubmitted = Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->route('teacher.survey')
                ->with('error', 'You have already submitted this survey.');
        }

        // Validate responses
        $request->validate([
            'responses' => 'required|array|max:50',
            'responses.*' => 'required|string|max:500',
            'evaluatee_id' => 'required|integer',
            'subject_id' => 'nullable|integer',
        ]);

        $evaluateeId = $request->input('evaluatee_id', $survey->evaluatee_id);
        $subjectId = $request->input('subject_id');

        // Transactional save to prevent partial submissions
        DB::transaction(function () use ($survey, $request, $evaluateeId, $subjectId) {
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
                        'response' => strip_tags($answer),
                    ]
                );
            }
            RunSentimentAnalysis::dispatch();
        });

        return redirect()->route('teacher.survey')
            ->with('success', 'Survey submitted successfully!');
    }
}
