<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Semester;
use App\Models\StudentStatus;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding form.
     *
     * Students and teachers are presented with the active semester's course
     * offerings so they can identify which ones they belong to.
     */
    public function showUploadForm()
    {
        $activeSemester = Semester::getActive();
        $user           = Auth::user();

        // Load offerings for the active semester with subject + teacher for display
        $offerings = $activeSemester
            ? CourseOffering::with(['subject', 'teacher'])
                ->where('semester_id', $activeSemester->id)
                ->orderBy('group_number')
                ->get()
            : collect();

        return view('onboarding.upload', compact('activeSemester', 'offerings', 'user'));
    }

    /**
     * Process onboarding submission.
     *
     * Teachers: validate that each selected offering exists and belongs to them.
     *   - If an offering exists but is assigned to someone else → block, contact admin.
     *   - If an offering exists and has no teacher → assign this teacher to it.
     *   - If an offering exists and already belongs to this teacher → no-op.
     *
     * Students: validate that each selected offering exists.
     *   - If the offering doesn't exist → block, contact admin.
     *   - If already enrolled → no-op (idempotent).
     *   - Otherwise → create Enrollment with the default 'regular' status.
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'offering_ids'   => 'required|array|min:1',
            'offering_ids.*' => 'required|ulid|exists:course_offerings,id',
        ]);

        $activeSemester = Semester::getActive();
        $user           = Auth::user();
        $blocked        = [];

        if ($user->hasRole('teacher')) {
            foreach ($request->offering_ids as $offeringId) {
                $offering = CourseOffering::find($offeringId);

                // Offering belongs to a different teacher → block
                if ($offering->teacher_id && $offering->teacher_id !== $user->id) {
                    $blocked[] = $offering->load('subject')->subject->course_code
                        . ' — ' . $offering->section_name;
                    continue;
                }

                // Unassigned → assign this teacher
                if (! $offering->teacher_id) {
                    $offering->update(['teacher_id' => $user->id]);
                }
                // Already theirs → no-op
            }

            if (! empty($blocked)) {
                $list = implode(', ', $blocked);
                return back()->with(
                    'error',
                    "The following sections are assigned to another teacher: {$list}. Please contact the administrator."
                );
            }

            return redirect()->route('teacher.dashboard')
                ->with('success', 'Sections confirmed successfully!');
        }

        // ── Student path ───────────────────────────────────────────────────────

        // Resolve the default student status (e.g. 'regular')
        $defaultStatus = StudentStatus::where('name', 'regular')->first();

        if (! $defaultStatus) {
            return back()->with(
                'error',
                'Student status configuration is missing. Please contact the administrator.'
            );
        }

        foreach ($request->offering_ids as $offeringId) {
            $offering = CourseOffering::find($offeringId);

            // Safety: offering must belong to the active semester
            if ($offering->semester_id !== $activeSemester?->id) {
                $blocked[] = $offering->load('subject')->subject->course_code
                    . ' — ' . $offering->section_name;
                continue;
            }

            // Already enrolled → skip
            $alreadyEnrolled = Enrollment::where('offering_id', $offeringId)
                ->where('student_id', $user->id)
                ->exists();

            if (! $alreadyEnrolled) {
                Enrollment::create([
                    'offering_id'       => $offeringId,
                    'student_id'        => $user->id,
                    'student_status_id' => $defaultStatus->id,
                ]);
            }
        }

        if (! empty($blocked)) {
            $list = implode(', ', $blocked);
            return back()->with(
                'error',
                "The following sections are not available for this semester: {$list}. Please contact the administrator."
            );
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'Enrollment confirmed successfully!');
    }
}