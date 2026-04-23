<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Semester;
use App\Models\EnrollmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Show available offerings for the active semester.
     */
    public function index()
    {
        $activeSemester = Semester::current();
        $availableOfferings = collect(); // Default to empty collection

        if ($activeSemester) {
            $enrolledIds = Enrollment::where('student_id', Auth::id())->pluck('offering_id');

            $query = CourseOffering::with(['subject', 'teacher', 'offeringType'])
                ->where('semester_id', $activeSemester->id)
                ->whereNotIn('id', $enrolledIds)
                ->whereNull('deleted_at');

            // Apply search if present
            if ($search = request('search')) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('subject', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%$search%")->orWhere('course_code', 'like', "%$search%");
                    })->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%$search%");
                    });
                });
            }

            $availableOfferings = $query->paginate(8)->withQueryString();
        }

        if (request()->ajax()) {
            return response()->json([
                // Make sure this path is 100% correct!
                'html' => view('student.enrollments._offering_cards', compact('availableOfferings'))->render()
            ]);
        }

        // Standard view variables
        $myEnrollments = Enrollment::where('student_id', Auth::id())->latest()->get();

        return view('student.enrollments.index', compact('activeSemester', 'availableOfferings', 'myEnrollments'));
    }

    /**
     * Student self-enrolls into an offering.
     */
    public function store(Request $request): RedirectResponse
    {
        $offering = CourseOffering::findOrFail($request->offering_id);
        $user = Auth::user();

        // Check if the student is already enrolled in A DIFFERENT section of the SAME SUBJECT
        $existingEnrollment = Enrollment::where('student_id', $user->id)
            ->whereHas('offering', function($query) use ($offering) {
                $query->where('subject_id', $offering->subject_id)
                    ->where('semester_id', $offering->semester_id);
            })->first();

        if ($existingEnrollment) {
            // Option A: Automatically swap them
            $existingEnrollment->delete();
        }

        // Create the new enrollment
        Enrollment::create([
            'offering_id'        => $offering->id,
            'student_id'         => $user->id,
            'enrollment_type_id' => EnrollmentType::whereName('block-enrolled')->first()?->id ?? 1,
        ]);

        // IMPORTANT: Clear any cached surveys for this user if you are using caching
        // artisan call('optimize:clear'); // Not recommended in code, but ensure cache is fresh

        return redirect()->route('student.dashboard')
            ->with('success', "Course changed to {$offering->subject->name} successfully.");
    }

    /**
     * Student drops (unenrolls from) a course.
     */
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        // Students can only drop their own enrollments
        if ($enrollment->student_id !== Auth::id()) {
            abort(403);
        }

        $enrollment->delete();

        return redirect()->route('student.enrollments.index')
                         ->with('success', 'You have been unenrolled from the course.');
    }
}
