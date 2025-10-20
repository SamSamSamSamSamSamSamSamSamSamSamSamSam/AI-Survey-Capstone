<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Survey;
use App\Models\Response;
use App\Models\User;
use App\Http\Controllers\Controller;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Enrolled subjects
        $subjects = $user->enrolledSubjects()->with('teachers')->get();

        // Active surveys assigned to this student 
        $activeSurveys = Survey::where('is_active', true)
            ->where('target_role', 'student')
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->with('subject', 'evaluatee')
            ->get();

        // Surveys already answered
        $answeredSurveyIds = Response::where('evaluator_id', $user->id)
            ->pluck('survey_id')
            ->unique();

        // Recent results 
        $recentResponses = Response::with('survey.subject')
            ->where('evaluator_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
    

        return view('student.dashboard', [
            'subjects' => $subjects,
            'activeSurveys' => $activeSurveys,
            'answeredSurveyIds' => $answeredSurveyIds,
            'recentResponses' => $recentResponses,
        ]);
    }
}
