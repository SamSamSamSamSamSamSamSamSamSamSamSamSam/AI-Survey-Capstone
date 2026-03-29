<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Semester;
use App\Jobs\RunSentimentAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSurveyController extends Controller
{
    public function index()
    {
        $student        = auth()->user();
        $activeSemester = Semester::getActive();

        // Subjects scoped to active semester
        $studentSubjects = $activeSemester
            ? $student->enrolledSubjectsForSemester($activeSemester->id)
                      ->select('subjects.id', 'subject_student.group')
                      ->get()
            : collect();

        $subjectIds = $studentSubjects->pluck('id');
        $groups     = $studentSubjects->pluck('group')->unique();

        $survey = Survey::with(['subject', 'evaluatee'])
            ->where('is_active', true)
            ->whereIn('target_role', ['student', 'both'])
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('group', $groups)
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.survey', compact('survey', 'activeSemester'));
    }

    public function show(Survey $survey)
    {
        $survey->load(['questions', 'evaluatee', 'subject']);
        $student        = auth()->user();
        $activeSemester = Semester::getActive();

        // Enrollment check scoped to active semester
        $isEnrolled = $activeSemester
            ? $student->enrolledSubjectsForSemester($activeSemester->id)
                      ->where('subjects.id', $survey->subject_id)
                      ->wherePivot('group', $survey->group)
                      ->exists()
            : false;

        if (!$isEnrolled) {
            return redirect()->route('student.survey')
                ->with('error', 'You are not enrolled in this subject or group for the current semester.');
        }

        $alreadySubmitted = Response::where('survey_id', $survey->id)
            ->where('evaluator_id', auth()->id())
            ->exists();

        return view('student.survey_take', compact('survey', 'alreadySubmitted'));
    }

    public function submit(Request $request, Survey $survey)
    {
        $student        = auth()->user();
        $activeSemester = Semester::getActive();

        // Enrollment check scoped to active semester
        $isEnrolled = $activeSemester
            ? $student->enrolledSubjectsForSemester($activeSemester->id)
                      ->where('subjects.id', $survey->subject_id)
                      ->wherePivot('group', $survey->group)
                      ->exists()
            : false;

        if (!$isEnrolled) {
            abort(403, 'Unauthorized submission.');
        }

        $request->validate([
            'responses'    => 'required|array|max:100',
            'responses.*'  => 'required|string|max:500',
            'evaluatee_id' => 'required|integer',
            'subject_id'   => 'required|integer',
        ]);

        $evaluateeId = $request->input('evaluatee_id', $survey->evaluatee_id);
        $subjectId   = $request->input('subject_id', $survey->subject_id);

        DB::transaction(function () use ($survey, $request, $evaluateeId, $subjectId, $student) {
            foreach ($request->responses as $questionId => $answer) {
                Response::updateOrCreate(
                    [
                        'survey_id'    => $survey->id,
                        'question_id'  => $questionId,
                        'evaluator_id' => $student->id,
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

        return redirect()->route('student.survey')
            ->with('success', 'Survey submitted successfully!');
    }
}