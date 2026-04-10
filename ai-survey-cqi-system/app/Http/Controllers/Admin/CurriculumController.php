<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCurriculumRequest;
use App\Http\Requests\Admin\UpdateCurriculumRequest;
use App\Models\Curriculum;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function index(Request $request): View
    {
        $programs = Program::orderBy('name')->get();

        $query = Curriculum::with('program')->withTrashed();

        if ($programId = $request->input('program_id')) {
            $query->where('program_id', $programId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('curriculum_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') === 'inactive') {
            $query->whereNull('deleted_at')->where('is_active', false);
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('deleted_at'); // default: show all non-deleted
        }

        $curricula = $query->latest()->paginate(15)->withQueryString();

        return view('admin.curricula.index', compact('curricula', 'programs'));
    }

    public function create(): View
    {
        $programs = Program::orderBy('name')->get();
        return view('admin.curricula.create', compact('programs'));
    }

    public function store(StoreCurriculumRequest $request): RedirectResponse
    {
        Curriculum::create($request->validated());

        return redirect()->route('admin.curricula.index')
                         ->with('success', 'Curriculum created successfully.');
    }

    public function show(Curriculum $curriculum): View
    {
        $curriculum->load('program');

        // Prospectus entries for this curriculum grouped by year + semester
        $grouped = $curriculum->prospectuses()
            ->with('subject')
            ->whereNull('deleted_at')
            ->orderBy('year_level')
            ->orderBy('semester_number')
            ->get()
            ->groupBy(fn ($p) => $p->year_level_label . ' — ' . $p->semester_label);

        return view('admin.curricula.show', compact('curriculum', 'grouped'));
    }

    public function edit(Curriculum $curriculum): View
    {
        $programs = Program::orderBy('name')->get();
        return view('admin.curricula.edit', compact('curriculum', 'programs'));
    }

    public function update(UpdateCurriculumRequest $request, Curriculum $curriculum): RedirectResponse
    {
        $curriculum->update($request->validated());

        return redirect()->route('admin.curricula.index')
                         ->with('success', 'Curriculum updated.');
    }

    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        if ($curriculum->prospectuses()->whereNull('deleted_at')->exists()) {
            return back()->with('error', 'Cannot archive a curriculum that has active prospectus entries. Remove them first.');
        }

        $curriculum->delete();

        return redirect()->route('admin.curricula.index')
                         ->with('success', 'Curriculum archived.');
    }

    public function restore(string $id): RedirectResponse
    {
        $curriculum = Curriculum::withTrashed()->findOrFail($id);
        $curriculum->restore();

        return redirect()->route('admin.curricula.index')
                         ->with('success', 'Curriculum restored.');
    }

    /**
     * Toggle the is_active flag for a curriculum.
     * Multiple curricula per program can be active simultaneously.
     */
    public function toggleActive(Curriculum $curriculum): RedirectResponse
    {
        $curriculum->toggleActive();

        $status = $curriculum->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$curriculum->curriculum_code} has been {$status}.");
    }
}
