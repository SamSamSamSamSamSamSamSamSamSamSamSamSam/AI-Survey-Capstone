<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Semester;
use App\Http\Controllers\Controller;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user           = Auth::user();
        $activeSemester = Semester::getActive();

        // Enrolled subjects scoped to active semester
        $subjects = $activeSemester
            ? $user->enrolledSubjectsForSemester($activeSemester->id)->with('teachers')->get()
            : collect();

        // Active surveys for this student — scoped to active semester
        $activeSurveys = Survey::where('is_active', true)
            ->where('target_role', 'student')
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->with('subject', 'evaluatee')
            ->get();

        // Surveys already answered
        $answeredSurveyIds = Response::where('evaluator_id', $user->id)
            ->pluck('survey_id')
            ->unique();

        // Recent responses (all time — historical)
        $recentResponses = Response::with('survey.subject')
            ->where('evaluator_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', [
            'subjects'          => $subjects,
            'activeSurveys'     => $activeSurveys,
            'answeredSurveyIds' => $answeredSurveyIds,
            'recentResponses'   => $recentResponses,
            'activeSemester'    => $activeSemester,
        ]);
    }

    public function results()
    {
        $user    = Auth::user();
        $results = [];

        $responses = Response::with(['survey.subject'])
            ->where('evaluator_id', $user->id)
            ->get()
            ->groupBy('survey_id');

        foreach ($responses as $surveyId => $group) {
            $survey   = $group->first()->survey;
            $ratings  = $group->filter(fn($r) => is_numeric($r->response))->pluck('response');
            $comments = $group->filter(fn($r) => !is_numeric($r->response))->pluck('response');

            $results[] = [
                'course'   => $survey->subject->course_code ?? 'N/A',
                'score'    => $ratings->count() ? round($ratings->avg(), 2) : 'N/A',
                'comments' => $comments->values()->all(),
            ];
        }

        return view('student.results', compact('results'));
    }
}