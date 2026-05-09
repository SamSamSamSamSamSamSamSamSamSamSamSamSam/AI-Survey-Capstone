<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseOfferingRequest;
use App\Http\Requests\Admin\UpdateCourseOfferingRequest;
use App\Models\CourseOffering;
use App\Models\OfferingType;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseOfferingController extends Controller
{
    public function index(Request $request)
    {
        $semesters      = Semester::orderByDesc('academic_start_year')->orderByDesc('semester_number')->get();
        $activeSemester = Semester::current();

        $subjects       = Subject::orderBy('course_code')->get();
        $teachers       = User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))->orderBy('name')->get();

        $selectedSemesterId = $request->input('semester_id', $activeSemester?->id);

        $query = CourseOffering::with(['subject', 'semester', 'teacher', 'offeringType'])
                               ->withTrashed();

        if ($selectedSemesterId) {
            $query->where('semester_id', $selectedSemesterId);
        }

        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($teacherId = $request->input('teacher_id')) {
            $query->where('teacher_id', $teacherId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('subject', fn ($sub) =>
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('course_code', 'like', "%{$search}%")
                )->orWhereHas('teacher', fn ($teach) =>
                    $teach->where('name', 'like', "%{$search}%")
                );
            });
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('course_offerings.deleted_at');
        }

        $offerings = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.offerings.index', compact('offerings', 'semesters', 'activeSemester', 'selectedSemesterId', 'subjects', 'teachers'))->fragment('offerings-table')
            ]);
        }

        return view('admin.offerings.index', compact(
            'offerings',
            'semesters',
            'activeSemester',
            'selectedSemesterId',
            'subjects',
            'teachers'
        ));
    }

    public function create(): View
    {
        $subjects      = Subject::orderBy('course_code')->get();
        $offering = new CourseOffering();
        $semesters     = Semester::orderByDesc('academic_start_year')->orderByDesc('semester_number')->get();
        $activeSemesterId = Semester::where('is_active', true)->value('id');
        $offeringTypes = OfferingType::orderBy('name')->get();
        $facultyRole   = Role::whereName('faculty')->first();
        $teachers      = $facultyRole
            ? User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))->orderBy('name')->get()
            : collect();

        return view('admin.offerings.create', compact('subjects', 'semesters', 'offeringTypes', 'teachers', 'activeSemesterId', 'offering'));
    }

    public function store(StoreCourseOfferingRequest $request): RedirectResponse
    {
        CourseOffering::create($request->validated());

        return redirect()->route('admin.offerings.index')
                         ->with('success', 'Course offering created.');
    }

    public function show(CourseOffering $offering): View
    {
        $offering->load(['subject', 'semester', 'teacher', 'offeringType', 'enrollments.student', 'enrollments.enrollmentType']);
        return view('admin.offerings.show', compact('offering'));
    }

    public function edit(CourseOffering $offering): View
    {
        $subjects      = Subject::orderBy('course_code')->get();
        $semesters     = Semester::orderByDesc('academic_start_year')->get();
        $offeringTypes = OfferingType::orderBy('name')->get();
        $teachers      = User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))->orderBy('name')->get();

        return view('admin.offerings.edit', compact('offering', 'subjects', 'semesters', 'offeringTypes', 'teachers'));
    }

    public function update(UpdateCourseOfferingRequest $request, CourseOffering $offering): RedirectResponse
    {
        $offering->update($request->validated());

        return redirect()->route('admin.offerings.index')
                         ->with('success', 'Course offering updated.');
    }

    public function destroy(CourseOffering $offering): RedirectResponse
    {
        $offering->delete();

        return redirect()->route('admin.offerings.index')
                         ->with('success', 'Course offering archived.');
    }

    public function restore(string $id): RedirectResponse
    {
        $offering = CourseOffering::withTrashed()->findOrFail($id);

        $exists = CourseOffering::where('subject_id', $offering->subject_id)
                                ->where('semester_id', $offering->semester_id)
                                ->where('group_number', $offering->group_number)
                                ->exists();

        if ($exists) {
            return redirect()->route('admin.offerings.index')
                            ->with('error', "Cannot restore: An active offering for this subject, semester, and group already exists.");
        }

        $offering->restore();

        return redirect()->route('admin.offerings.index')->with('success', 'Course offering restored.');
    }
}