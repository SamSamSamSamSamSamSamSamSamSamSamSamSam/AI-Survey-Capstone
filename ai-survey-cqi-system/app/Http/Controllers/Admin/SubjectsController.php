<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectsController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('course_code')->paginate(10);
        return view('admin.subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_code' => 'required|string|max:255|unique:subjects,course_code',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Subject::create($data);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject added successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
