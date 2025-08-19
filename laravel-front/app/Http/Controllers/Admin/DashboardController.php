<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $departmentData = [
            'Jan' => 3.8,
            'Feb' => 4.0,
            'Mar' => 4.1,
            'Apr' => 4.0,
            'May' => 4.2,
        ];

        $topFaculty = [
            ['name' => 'Dr. Alice', 'score' => 4.9],
            ['name' => 'Prof. Bob',  'score' => 4.8],
            ['name' => 'Dr. Carol',  'score' => 4.7],
        ];

        $comments = [
            ['author' => 'Student A', 'text' => 'Great clarity and helpful feedback.'],
            ['author' => 'Student B', 'text' => 'Needs to improve timeliness of grading.'],
            ['author' => 'Student C', 'text' => 'Very engaging lectures.'],
        ];

        $facultyStats = [
            ['name' => 'Dr. Alice', 'status' => 'Active',   'score' => 4.9],
            ['name' => 'Prof. Bob',  'status' => 'On Leave', 'score' => 4.8],
            ['name' => 'Dr. Carol',  'status' => 'Active',   'score' => 4.7],
        ];

        return view('admin.dashboard', compact('departmentData', 'topFaculty', 'comments', 'facultyStats'));
    }
}

