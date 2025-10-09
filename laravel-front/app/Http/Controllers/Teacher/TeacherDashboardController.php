<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\User;


class TeacherDashboardController extends Controller
{
    public function index()
    {
        $classes = [
            ['code'=>'CS101','title'=>'Intro to Programming','students'=>120],
            ['code'=>'CS201','title'=>'Data Structures','students'=>80],
        ];

        $feedbackSummary = [
            'average_score' => 4.3,
            'recent_comments' => [
                ['author'=>'Student A','text'=>'Clear explanations.'],
                ['author'=>'Student B','text'=>'More examples please.'],
            ]
        ];

        return view('teacher.dashboard', compact('classes','feedbackSummary'));
    }

    public function survey()
    {
        $survey = Survey::where('is_active', true)
        ->whereIn('target_role', ['teacher', 'both'])
        ->get();

        return view('teacher.survey', compact('survey'));
    }

    public function reviews()
    {
        $reviews = [
            ['student'=>'Student A','rating'=>5,'comment'=>'Excellent.'],
            ['student'=>'Student B','rating'=>4,'comment'=>'Good, needs improvement.'],
        ];

        return view('teacher.reviews', compact('reviews'));
    }
}
