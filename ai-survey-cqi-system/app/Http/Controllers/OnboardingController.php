<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Semester;

class OnboardingController extends Controller
{
    /**
     * Show the manual subject entry form.
     */
    public function showUploadForm()
    {
        $activeSemester = Semester::getActive();

        // Load existing subjects from DB for the autocomplete datalist
        $existingSubjects = Subject::orderBy('course_code')->get();

        return view('onboarding.upload', compact('activeSemester', 'existingSubjects'));
    }

    /**
     * Save the manually entered subjects for the active semester.
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'subjects'          => 'required|array|min:1',
            'subjects.*.code'   => 'required|string|max:50',
            'subjects.*.group'  => 'nullable|string|max:50',
        ]);

        $activeSemester = Semester::getActive();
        $user           = Auth::user();

        foreach ($request->subjects as $entry) {
            $courseCode = strtoupper(trim($entry['code']));
            $group      = trim($entry['group'] ?? '') ?: null;

            if (empty($courseCode)) continue;

            // Find or create the subject
            $subject = Subject::firstOrCreate(
                ['course_code' => $courseCode],
                ['name' => null, 'description' => 'Added via manual onboarding']
            );

            $pivotData = [
                'group'       => $group,
                'semester_id' => $activeSemester?->id,
            ];

            // Attach to the correct pivot table based on role
            if ($user->hasRole('student')) {
                // Check if already enrolled in this subject for this semester
                $alreadyEnrolled = $user->enrolledSubjects()
                    ->wherePivot('semester_id', $activeSemester?->id)
                    ->where('subjects.id', $subject->id)
                    ->exists();

                if (!$alreadyEnrolled) {
                    $user->enrolledSubjects()->attach($subject->id, $pivotData);
                }
            } elseif ($user->hasRole('teacher')) {
                $alreadyAssigned = $user->teachingSubjects()
                    ->wherePivot('semester_id', $activeSemester?->id)
                    ->where('subjects.id', $subject->id)
                    ->exists();

                if (!$alreadyAssigned) {
                    $user->teachingSubjects()->attach($subject->id, $pivotData);
                }
            }
        }

        // Redirect based on role
        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard')
                ->with('success', 'Subjects saved successfully!');
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'Subjects saved successfully!');
    }
}