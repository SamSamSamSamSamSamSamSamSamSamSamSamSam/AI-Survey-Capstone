<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;

class DepartmentsController extends Controller
{
    public function index()
    {
        // Get all faculty members (teachers)
        $faculty = User::whereHas('roles', function($query) {
            $query->where('name', 'teacher');
        })->with(['assignedSubjects', 'roles'])->get();

        // Get all subjects for the filter dropdown
        $subjects = Subject::orderBy('name')->get();

        return view('admin.department', compact('faculty', 'subjects'));
    }
}