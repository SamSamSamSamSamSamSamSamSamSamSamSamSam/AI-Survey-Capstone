<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Semester;
use App\Jobs\RunSentimentAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSurveyController extends Controller
{
    /**
     * Show all surveys available to the teacher
     * scoped to the currently active semester.
     */
    public function index()
    {
        $activeSemester = Semester::getActive();

        $survey = Survey::where('is_active', true)
            ->whereIn('target_role', ['teacher', 'both'])
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teacher.survey', compact('survey', 'activeSemester'));
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
        $alreadySubmitted = Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->route('teacher.survey')
                ->with('error', 'You have already submitted this survey.');
        }

        $request->validate([
            'responses'    => 'required|array|max:50',
            'responses.*'  => 'required|string|max:500',
            'evaluatee_id' => 'required|integer',
            'subject_id'   => 'nullable|integer',
        ]);

        $evaluateeId = $request->input('evaluatee_id', $survey->evaluatee_id);
        $subjectId   = $request->input('subject_id');

        DB::transaction(function () use ($survey, $request, $evaluateeId, $subjectId) {
            foreach ($request->responses as $questionId => $answer) {
                Response::updateOrCreate(
                    [
                        'survey_id'    => $survey->id,
                        'question_id'  => $questionId,
                        'evaluator_id' => auth()->id(),
                        'evaluatee_id' => $evaluateeId,
                        'subject_id'   => $subjectId,
                    ],
                    [
                        'response'    => strip_tags($answer),
                        'semester_id' => $survey->semester_id,
                    ]
                );
            }

            RunSentimentAnalysis::dispatch();
        });

        return redirect()->route('teacher.survey')
            ->with('success', 'Survey submitted successfully!');
    }
}