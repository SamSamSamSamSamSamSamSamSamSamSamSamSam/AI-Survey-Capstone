<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\Semester;

class DepartmentsController extends Controller
{
    public function index()
    {
        $activeSemester = Semester::getActive();

        // Load all faculty with their course offerings for the active semester,
        // including the subject and semester details for display.
        $faculty = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->with([
                'roles',
                'courseOfferings' => fn($q) => $activeSemester
                    ? $q->where('semester_id', $activeSemester->id)->with('subject')
                    : $q->with('subject'),
            ])
            ->orderBy('name')
            ->get();

        // Subjects for the filter dropdown
        $subjects = Subject::orderBy('name')->get();

        return view('admin.department', compact('faculty', 'subjects', 'activeSemester'));
    }
}