<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSemesterRequest;
use App\Http\Requests\Admin\UpdateSemesterRequest;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(): View
    {
        $semesters = Semester::latest('academic_start_year')
                             ->orderByDesc('semester_number')
                             ->paginate(15);

        return view('admin.semesters.index', compact('semesters'));
    }

    public function create(): View
    {
        return view('admin.semesters.create');
    }

    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        Semester::create($request->validated());

        return redirect()->route('admin.semesters.index')
                         ->with('success', 'Semester created.');
    }

    public function edit(Semester $semester): View
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        // Prevent editing is_active directly through the form — use activate/deactivate
        $semester->update($request->safe()->except('is_active'));

        return redirect()->route('admin.semesters.index')
                         ->with('success', 'Semester updated.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->is_active) {
            return back()->with('error', 'Cannot delete the active semester. Deactivate it first.');
        }

        if ($semester->offerings()->exists()) {
            return back()->with('error', 'Cannot delete a semester that has course offerings.');
        }

        $semester->delete();

        return redirect()->route('admin.semesters.index')
                         ->with('success', 'Semester deleted.');
    }

    /**
     * Activate a semester — deactivates all others automatically.
     */
    public function activate(Semester $semester): RedirectResponse
    {
        $semester->activate();

        return redirect()->route('admin.semesters.index')
                         ->with('success', "{$semester->full_label} is now the active semester.");
    }

    /**
     * Deactivate the current semester (no active semester state).
     */
    public function deactivate(Semester $semester): RedirectResponse
    {
        $semester->deactivate();

        return redirect()->route('admin.semesters.index')
                         ->with('success', 'Semester deactivated. No active semester is set.');
    }
}
