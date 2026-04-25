<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GlobalSurveyController extends Controller
{
    public function create(): View
    {
        $activeSemester = Semester::current();
        $officialTemplate = SurveyTemplate::where('is_official', true)
                                           ->where('is_active', true)
                                           ->get();

        $roles = Role::orderBy('name')->get();

        // Count offerings that don't yet have a survey this semester
        $offeringsWithoutSurvey = 0;
        if ($activeSemester) {
            $offeringsWithoutSurvey = CourseOffering::where('semester_id', $activeSemester->id)
                ->whereNull('deleted_at')
                ->whereDoesntHave('surveys', fn ($q) => $q->whereNull('deleted_at'))
                ->count();
        }

        // Show expected respondent counts per role for informational purposes
        $facultyCount = User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))->count();
        $adminCount   = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count();

        return view('admin.surveys.global-assign', compact(
            'activeSemester',
            'officialTemplate',
            'roles',
            'offeringsWithoutSurvey',
            'facultyCount',
            'adminCount',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'target_role_id' => ['required', 'exists:roles,id'],
            'start_date'     => ['required', 'date', 'before:end_date'],
            'end_date'       => ['required', 'date', 'after:start_date'],
            'skip_existing'  => ['boolean'],
        ]);

        $activeSemester = Semester::current();
        $targetRole = Role::findOrFail($request->target_role_id);

        if (! $activeSemester) {
            return back()->with('error', 'No active semester found.');
        }

        $officialTemplate = SurveyTemplate::where('is_official', true)
                                           ->where('is_active', true)
                                           ->withCount('questions')
                                           ->first();

        if (! $officialTemplate || $officialTemplate->questions_count === 0) {
            return back()->with('error', 'No active official template found, or the template has no questions.');
        }

        // Fetch target offerings
        $offeringsQuery = CourseOffering::where('semester_id', $activeSemester->id)
                                        ->whereNull('deleted_at');

        if ($request->boolean('skip_existing', true)) {
            $offeringsQuery->whereDoesntHave('surveys', fn ($q) => $q->whereNull('deleted_at'));
        }

        $offerings = $offeringsQuery->get();

        if ($offerings->isEmpty()) {
            return back()->with('error', 'No eligible offerings found. All offerings may already have surveys.');
        }

        $created = 0;

        DB::transaction(function () use ($offerings, $officialTemplate, $request, &$created) {
            foreach ($offerings as $offering) {
                $survey = Survey::create([
                    'offering_id'    => $offering->id,
                    'created_by'     => Auth::id(),
                    'template_id'    => $officialTemplate->id,
                    'target_role_id' => $request->target_role_id,
                    'title'          => "Faculty Evaluation — {$offering->subject->course_code}",
                    'description'    => "Official faculty evaluation survey for {$offering->subject->name}.",
                    'is_active'      => true,
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                ]);

                // Copy template questions into survey
                $officialTemplate->copyQuestionsTo($survey);
                $created++;
            }
        });

        $roleLabel = match ($targetRole->name) {
            'faculty' => 'all faculty (excluding each offering\'s own teacher)',
            'admin'   => 'all admin users',
            'student' => 'enrolled students per offering',
            default   => $targetRole->name,
        };

        return redirect()->route('admin.surveys.index')
                         ->with('success', "{$created} survey(s) created and activated for {$roleLabel}.");
    }

    private function buildDescription(string $roleName, CourseOffering $offering): string
    {
        return match ($roleName) {
            'faculty' => "Peer evaluation survey for {$offering->subject->name}. Open to all faculty except the assigned teacher.",
            'admin'   => "Administrative evaluation for {$offering->subject->name}.",
            'student' => "Official faculty evaluation survey for {$offering->subject->name}.",
            default   => "Survey for {$offering->subject->name}.",
        };
    }
}
