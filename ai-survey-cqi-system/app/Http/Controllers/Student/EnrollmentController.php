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
        $request->validate([
            'offering_id' => ['required', 'exists:course_offerings,id'],
        ]);

        $activeSemester = Semester::current();

        if (! $activeSemester) {
            return back()->with('error', 'Enrollment is not available. No active semester is set.');
        }

        $offering = CourseOffering::where('id', $request->offering_id)
                                  ->where('semester_id', $activeSemester->id)
                                  ->whereNull('deleted_at')
                                  ->firstOrFail();

        // Prevent duplicate enrollment
        $alreadyEnrolled = Enrollment::where('offering_id', $offering->id)
                                     ->where('student_id', Auth::id())
                                     ->exists();

        if ($alreadyEnrolled) {
            return back()->with('error', 'You are already enrolled in this course.');
        }

        // Default status: 'regular' — adjust as needed
        $defaultEnrollmentType = EnrollmentType::whereName('block-enrolled')->first()
                      ?? EnrollmentType::first();

        Enrollment::create([
            'offering_id'       => $offering->id,
            'student_id'        => Auth::id(),
            'enrollment_type_id' => $defaultEnrollmentType?->id,
        ]);

        return redirect()->route('student.enrollments.index')
                         ->with('success', "Enrolled in {$offering->subject->name} successfully.");
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
