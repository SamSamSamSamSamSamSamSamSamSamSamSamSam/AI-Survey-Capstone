<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\EnrollmentType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * List enrollments for a specific offering.
     */
    public function index(CourseOffering $offering): View
    {
        $offering->load(['subject', 'semester', 'teacher']);

        $enrollments = Enrollment::with(['student', 'enrollmentType'])
                                 ->where('offering_id', $offering->id)
                                 ->latest()
                                 ->paginate(20);

        return view('admin.enrollments.index', compact('offering', 'enrollments'));
    }

    /**
     * Show form to add a student to an offering.
     */
    public function create(CourseOffering $offering): View
    {
        $offering->load('subject');

        // Only show students not yet enrolled in this offering
        $enrolledIds = Enrollment::where('offering_id', $offering->id)->pluck('student_id');

        $students = User::whereHas('roles', fn ($q) => $q->where('name', 'student'))
                        ->whereNotIn('id', $enrolledIds)
                        ->orderBy('name')
                        ->get();

        $statuses = EnrollmentType::orderBy('name')->get();

        return view('admin.enrollments.create', compact('offering', 'students', 'statuses'));
    }

    /**
     * Admin enrolls a student.
     */
    public function store(StoreEnrollmentRequest $request, CourseOffering $offering): RedirectResponse
    {
        Enrollment::create([
            'offering_id'       => $offering->id,
            'student_id'        => $request->student_id,
            'student_status_id' => $request->student_status_id,
        ]);

        return redirect()->route('admin.offerings.enrollments.index', $offering->id)
                         ->with('success', 'Student enrolled successfully.');
    }

    /**
     * Remove a student from an offering.
     */
    public function destroy(CourseOffering $offering, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->delete();

        return redirect()->route('admin.offerings.enrollments.index', $offering->id)
                         ->with('success', 'Enrollment removed.');
    }
}
