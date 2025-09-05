<?php

namespace App\Http\Controllers\Admin;

use App\Models\Survey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SurveyController extends Controller
{
    // Show create survey form
    public function create()
    {
        return view('admin.surveys.create');
    }

    // Store survey in DB
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
        ]);

        Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            // store as JSON
            'questions' => json_encode($request->questions),
        ]);

        return redirect()->route('admin.surveys.index')->with('success', 'Survey created successfully!');
    }


    public function view()
    {
        $surveys = Survey::all(); // fetch all surveys
        return view('admin.surveys.index', compact('surveys'));
    }
}
