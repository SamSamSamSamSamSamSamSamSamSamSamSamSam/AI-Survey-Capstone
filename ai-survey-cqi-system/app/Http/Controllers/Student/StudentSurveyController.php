<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Response;
use App\Jobs\RunSentimentAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSurveyController extends Controller
{
    /**
     * Show all surveys available to the logged-in student.
     */
    public function index()
    {
        $student = auth()->user();

        // Get the subjects and groups this student is enrolled in
        $studentSubjects = $student->enrolledSubjects()
            ->select('subjects.id', 'subject_student.group')
            ->get();

        $subjectIds = $studentSubjects->pluck('id');
        $groups = $studentSubjects->pluck('group')->unique();

        // Fetch surveys matching student's subjects and groups
        $survey = Survey::with(['subject', 'evaluatee'])
            ->where('is_active', true)
            ->whereIn('target_role', ['student', 'both'])
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('group', $groups)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.survey', compact('survey'));
    }

    /**
     * Show the survey form for the student to take.
     */
    public function show(Survey $survey)
    {
        $survey->load(['questions', 'evaluatee', 'subject']);
        $student = auth()->user();

        // Ensure student is enrolled in this subject and group
        $isEnrolled = $student->enrolledSubjects()
            ->where('subjects.id', $survey->subject_id)
            ->wherePivot('group', $survey->group)
            ->exists();

        if (! $isEnrolled) {
            return redirect()->route('student.survey')
                ->with('error', 'You are not enrolled in this subject or group.');
        }

        // Check if already submitted
        $alreadySubmitted = Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        return view('student.survey_take', compact('survey', 'alreadySubmitted'));
    }

    /**
     * Handle survey submission.
     */
     public function submit(Request $request, Survey $survey)
    {
        $student = auth()->user();

        // Ensure student is enrolled in this subject and group
        $isEnrolled = $student->enrolledSubjects()
            ->where('subjects.id', $survey->subject_id)
            ->wherePivot('group', $survey->group)
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Unauthorized submission.');
        }

        // Validate input
        $request->validate([
            'responses' => 'required|array|max:100', // adjust max to your question count
            'responses.*' => 'required|string|max:500',
            'evaluatee_id' => 'required|integer',
            'subject_id' => 'required|integer',
        ]);

        $evaluateeId = $request->input('evaluatee_id', $survey->evaluatee_id);
        $subjectId = $request->input('subject_id', $survey->subject_id);

        // Use DB transaction to prevent partial saves
        DB::transaction(function () use ($survey, $request, $evaluateeId, $subjectId, $student) {

            foreach ($request->responses as $questionId => $answer) {
                Response::updateOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'question_id' => $questionId,
                        'evaluator_id' => $student->id,
                        'evaluatee_id' => $evaluateeId,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'response' => strip_tags($answer),
                    ]
                );
            }

            // Dispatch sentiment analysis in the background
            RunSentimentAnalysis::dispatch();
        });

        return redirect()->route('student.survey')
            ->with('success', 'Survey submitted successfully!');
    }


}
