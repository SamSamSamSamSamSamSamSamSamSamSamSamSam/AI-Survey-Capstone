<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
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

    public function classes()
    {
        $classes = [
            ['code'=>'CS101','title'=>'Intro to Programming','students'=>120,'term'=>'2025S1'],
            ['code'=>'CS201','title'=>'Data Structures','students'=>80,'term'=>'2025S1'],
        ];

        return view('teacher.classes', compact('classes'));
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
