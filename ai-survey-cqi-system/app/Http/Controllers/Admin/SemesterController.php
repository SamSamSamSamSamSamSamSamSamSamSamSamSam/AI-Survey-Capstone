<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolYearRequest;
use App\Http\Requests\Admin\UpdateSemesterRequest;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SemesterController extends Controller
{
    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $grouped        = Semester::groupedByYear();           // Collection keyed by academic_start_year
        $activeSemester = Semester::current();
        $search         = request('search');

        // Apply search filter if present
        if ($search) {
            $grouped = Semester::where('name', 'like', "%{$search}%")
                ->orWhere('academic_start_year', 'like', "%{$search}%")
                ->orderByDesc('academic_start_year')
                ->orderBy('semester_number')
                ->get()
                ->groupBy('academic_start_year');
        }

        return view('admin.semesters.index', compact('grouped', 'activeSemester', 'search'));
    }

    // -------------------------------------------------------------------------
    // Create School Year (replaces old single-semester create)
    // -------------------------------------------------------------------------

    public function create(): View
    {
        return view('admin.semesters.create');
    }

    public function store(StoreSchoolYearRequest $request): RedirectResponse
    {
        $startYear     = (int) $request->validated()['academic_start_year'];
        $includeSummer = (bool) ($request->validated()['include_summer'] ?? false);

        $created = Semester::generateForYear($startYear, $includeSummer);

        $count   = $created->count();
        $ayLabel = "S.Y. {$startYear}–" . ($startYear + 1);

        if ($count === 0) {
            return redirect()->route('admin.semesters.index')
                ->with('info', "All semesters for {$ayLabel} already exist.");
        }

        return redirect()->route('admin.semesters.index')
            ->with('success', "{$count} semester(s) created for {$ayLabel}.");
    }

    // -------------------------------------------------------------------------
    // Edit / Update a single semester (name only — year & number stay fixed)
    // -------------------------------------------------------------------------

    public function edit(Semester $semester): View
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        // Only allow updating the display name; year and number are immutable post-creation.
        $semester->update($request->safe()->only('name'));

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester name updated.');
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

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
            ->with('success', "{$semester->full_label} has been deleted.");
    }

    // -------------------------------------------------------------------------
    // Activate / Deactivate
    // -------------------------------------------------------------------------

    /**
     * Activate a semester — automatically deactivates all others.
     */
    public function activate(Semester $semester): RedirectResponse
    {
        $semester->activate();

        return redirect()->route('admin.semesters.index')
            ->with('success', "{$semester->full_label} is now the active semester.");
    }

    /**
     * Deactivate the current semester (leaves no active semester).
     */
    public function deactivate(Semester $semester): RedirectResponse
    {
        if (! $semester->is_active) {
            return back()->with('info', 'That semester is already inactive.');
        }

        $semester->deactivate();

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester deactivated. No active semester is currently set.');
    }
}