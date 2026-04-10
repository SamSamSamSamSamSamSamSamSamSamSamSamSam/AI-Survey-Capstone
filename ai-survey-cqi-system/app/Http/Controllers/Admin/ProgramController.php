<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProgramRequest;
use App\Http\Requests\Admin\UpdateProgramRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $query = Program::withTrashed();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('program_code', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('deleted_at');
        }

        $programs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.programs.create');
    }

    public function store(StoreProgramRequest $request): RedirectResponse
    {
        Program::create($request->validated());

        return redirect()->route('admin.programs.index')
                         ->with('success', 'Program created successfully.');
    }

    public function show(Program $program): View
    {
        $program->load(['prospectuses.subject', 'prospectuses.offeringType']);
        return view('admin.programs.show', compact('program'));
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.edit', compact('program'));
    }

    public function update(UpdateProgramRequest $request, Program $program): RedirectResponse
    {
        $program->update($request->validated());

        return redirect()->route('admin.programs.index')
                         ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return redirect()->route('admin.programs.index')
                         ->with('success', 'Program archived.');
    }

    public function restore(string $id): RedirectResponse
    {
        $program = Program::withTrashed()->findOrFail($id);
        $program->restore();

        return redirect()->route('admin.programs.index')
                         ->with('success', 'Program restored.');
    }
}
