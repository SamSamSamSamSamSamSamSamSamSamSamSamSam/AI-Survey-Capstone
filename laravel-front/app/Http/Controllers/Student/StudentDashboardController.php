<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $surveys = [
            ['course'=>'CS101','status'=>'completed','score'=>4.5],
            ['course'=>'CS201','status'=>'pending','score'=>null],
        ];

        $recentResults = [
            ['course'=>'CS101','score'=>4.5,'instructor'=>'Dr. Alice'],
        ];

        return view('student.dashboard', compact('surveys','recentResults'));
    }

    public function results()
    {
        $results = [
            ['course'=>'CS101','score'=>4.5,'comments'=>['Great course.']],
            ['course'=>'CS201','score'=>3.9,'comments'=>[]],
        ];

        return view('student.results', compact('results'));
    }
}
