<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Program;
use App\Models\QuestionCategory;
use App\Models\Scale;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
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
            $query->whereNull('deleted_at');
        }

        $curricula = $query->latest()->paginate(15)->withQueryString();

        return view('admin.curricula.index', compact('curricula', 'programs'));
    }

    public function create(): View
    {
        $programs = Program::orderBy('name')->get();
        return view('admin.curricula.create', compact('programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id'      => ['required', 'exists:programs,id'],
            'curriculum_code' => ['required', 'string', 'max:100', 'unique:curricula,curriculum_code,NULL,id,program_id,' . $request->input('program_id')],
            'description'     => ['nullable', 'string', 'max:500'],
            'effective_year'  => ['required', 'digits:4', 'integer', 'min:2000'],
            'is_active'       => ['boolean'],
        ]);

        $curriculum = \App\Models\Curriculum::create([
            'program_id'      => $data['program_id'],
            'curriculum_code' => $data['curriculum_code'],
            'description'     => $data['description'] ?? null,
            'effective_year'  => $data['effective_year'],
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.curricula.show', $curriculum->id)
                         ->with('success', 'Curriculum created. Now add questions.');
    }

    /**
     * Show curriculum with prospectus grouped by year level + semester label.
     * Now loads semester relationship for correct label rendering.
     */
    public function show(\App\Models\Curriculum $curriculum): View
    {
        $curriculum->load('program');

        $grouped = $curriculum->prospectuses()
            ->with(['subject', 'semester'])   // ← semester eager loaded
            ->whereNull('deleted_at')
            ->orderBy('year_level')
            ->orderBy('semester_id')           // ← order by semester_id
            ->get()
            ->groupBy(fn ($p) => $p->year_level_label . ' — ' . $p->semester_short_label);

        $categories = QuestionCategory::orderBy('name')->get();
        $scales     = Scale::orderBy('name')->get();

        return view('admin.curricula.show', compact('curriculum', 'grouped', 'categories', 'scales'));
    }

    public function edit(\App\Models\Curriculum $curriculum): View
    {
        $programs = Program::orderBy('name')->get();
        return view('admin.curricula.edit', compact('curriculum', 'programs'));
    }

    public function update(Request $request, \App\Models\Curriculum $curriculum): RedirectResponse
    {
        $data = $request->validate([
            'program_id'      => ['required', 'exists:programs,id'],
            'curriculum_code' => ['required', 'string', 'max:100', "unique:curricula,curriculum_code,{$curriculum->id},id,program_id,{$curriculum->program_id}"],
            'description'     => ['nullable', 'string', 'max:500'],
            'effective_year'  => ['required', 'digits:4', 'integer', 'min:2000'],
            'is_active'       => ['boolean'],
        ]);

        $curriculum->update([
            'program_id'      => $data['program_id'],
            'curriculum_code' => $data['curriculum_code'],
            'description'     => $data['description'] ?? null,
            'effective_year'  => $data['effective_year'],
            'is_active'       => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.curricula.show', $curriculum->id)
                         ->with('success', 'Curriculum updated.');
    }

    public function destroy(\App\Models\Curriculum $curriculum): RedirectResponse
    {
        if ($curriculum->is_official ?? false) {
            return back()->with('error', 'The official university questionnaire cannot be deleted.');
        }

        if ($curriculum->prospectuses()->whereNull('deleted_at')->exists()) {
            return back()->with('error', 'Cannot archive a curriculum that has active prospectus entries.');
        }

        $curriculum->delete();

        return redirect()->route('admin.curricula.index')
                         ->with('success', 'Curriculum archived.');
    }

    public function restore(string $id): RedirectResponse
    {
        $curriculum = \App\Models\Curriculum::withTrashed()->findOrFail($id);
        $curriculum->restore();

        return redirect()->route('admin.curricula.index')
                         ->with('success', 'Curriculum restored.');
    }

    public function toggleActive(\App\Models\Curriculum $curriculum): RedirectResponse
    {
        $curriculum->toggleActive();
        $status = $curriculum->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$curriculum->curriculum_code} has been {$status}.");
    }

    // ── Template question management (unchanged) ──────────────────────────────

    public function storeQuestion(Request $request, \App\Models\Curriculum $curriculum): RedirectResponse
    {
        // delegate to SurveyTemplateController pattern — kept here for reference
        return back()->with('error', 'This action is handled by SurveyTemplateController.');
    }
}
