<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProspectusRequest;
use App\Models\Curriculum;
use App\Models\Program;
use App\Models\Prospectus;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectusController extends Controller
{
    /**
     * Show prospectus — select program first, then curriculum.
     * Grouped by year level + semester.
     */
    public function index(Request $request): View
    {
        $programs           = Program::orderBy('name')->get();
        $selectedProgram    = null;
        $curricula          = collect();
        $selectedCurriculum = null;
        $grouped            = collect();

        if ($programId = $request->input('program_id')) {
            $selectedProgram = Program::findOrFail($programId);
            $curricula       = Curriculum::forProgram($programId)
                                         ->whereNull('deleted_at')
                                         ->orderByDesc('effective_year')
                                         ->get();

            if ($curriculumId = $request->input('curriculum_id')) {
                $selectedCurriculum = Curriculum::findOrFail($curriculumId);

                $entries = Prospectus::with('subject')
                    ->where('curriculum_id', $curriculumId)
                    ->whereNull('deleted_at')
                    ->orderBy('year_level')
                    ->orderBy('semester_number')
                    ->get();

                $grouped = $entries->groupBy(
                    fn ($e) => $e->year_level_label . ' — ' . $e->semester_label
                );
            }
        }

        return view('admin.prospectus.index', compact(
            'programs',
            'selectedProgram',
            'curricula',
            'selectedCurriculum',
            'grouped',
        ));
    }

    public function create(Request $request): View
    {
        $programs  = Program::orderBy('name')->get();
        $subjects  = Subject::orderBy('course_code')->get();

        // Pre-load curricula if program is pre-selected (e.g. from query string)
        $curricula = collect();
        if ($programId = $request->input('program_id')) {
            $curricula = Curriculum::forProgram($programId)
                                    ->whereNull('deleted_at')
                                    ->orderByDesc('effective_year')
                                    ->get();
        }

        return view('admin.prospectus.create', compact('programs', 'subjects', 'curricula'));
    }

    public function store(StoreProspectusRequest $request): RedirectResponse
    {
        Prospectus::create($request->validated());

        return redirect()->route('admin.prospectus.index', [
                             'program_id'    => $request->program_id,
                             'curriculum_id' => $request->curriculum_id,
                         ])
                         ->with('success', 'Prospectus entry added.');
    }

    public function destroy(Prospectus $prospectus): RedirectResponse
    {
        $programId    = $prospectus->program_id;
        $curriculumId = $prospectus->curriculum_id;

        $prospectus->delete();

        return redirect()->route('admin.prospectus.index', [
                             'program_id'    => $programId,
                             'curriculum_id' => $curriculumId,
                         ])
                         ->with('success', 'Prospectus entry removed.');
    }
}
