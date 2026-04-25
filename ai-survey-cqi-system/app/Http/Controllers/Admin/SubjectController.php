<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subject::withTrashed();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('deleted_at');
        }

        $subjects = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('admin.subjects.partials.table', compact('subjects'));
        }

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')
                         ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject): View
    {
        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('admin.subjects.index')
                         ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
                         ->with('success', 'Subject archived.');
    }

        public function restore(string $id): RedirectResponse
    {
        $subjectToRestore = Subject::withTrashed()->findOrFail($id);
        $exists = Subject::where('course_code', $subjectToRestore->course_code)
                        ->exists(); 

        if ($exists) {
            return redirect()->route('admin.subjects.index')
                            ->with('error', "Cannot restore: A subject with code '{$subjectToRestore->course_code}' is already active.");
        }

        $subjectToRestore->restore();

        return redirect()->route('admin.subjects.index')
                        ->with('success', 'Subject restored successfully.');
    }
}
