<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\StudentStatus;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    public function edit(User $user)
    {
        $roles          = Role::all();
        $activeSemester = Semester::getActive();

        // For teachers: show all course offerings in the active semester so
        // admin can assign this teacher to any of them.
        $availableOfferings = collect();
        $assignedOfferingIds = collect();

        // For students: show all course offerings in the active semester so
        // admin can enroll/unenroll this student.
        $enrolledOfferingIds = collect();
        $studentStatuses     = collect();

        if ($activeSemester) {
            $availableOfferings = CourseOffering::with(['subject', 'teacher'])
                ->where('semester_id', $activeSemester->id)
                ->orderBy('section_name')
                ->get();

            if ($user->hasRole('teacher')) {
                $assignedOfferingIds = CourseOffering::where('semester_id', $activeSemester->id)
                    ->where('teacher_id', $user->id)
                    ->pluck('id');
            }

            if ($user->hasRole('student')) {
                $enrolledOfferingIds = Enrollment::where('student_id', $user->id)
                    ->whereHas('offering', fn($q) => $q->where('semester_id', $activeSemester->id))
                    ->pluck('offering_id');

                $studentStatuses = StudentStatus::all();
            }
        }

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'activeSemester',
            'availableOfferings',
            'assignedOfferingIds',
            'enrolledOfferingIds',
            'studentStatuses'
        ));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|max:255|unique:users,email,' . $user->id,
            'user_id_number'      => 'required|string|max:255|unique:users,user_id_number,' . $user->id,
            'password'            => 'nullable|string|min:6|confirmed',
            'roles'               => 'nullable|array',
            'roles.*'             => 'exists:roles,id',
            // Teacher offering assignments
            'offering_ids'        => 'nullable|array',
            'offering_ids.*'      => 'exists:course_offerings,id',
            // Student enrollment
            'enroll_offering_ids'       => 'nullable|array',
            'enroll_offering_ids.*'     => 'exists:course_offerings,id',
            'student_status_id'         => 'nullable|exists:student_statuses,id',
        ]);

        // ── Update basic info ──────────────────────────────────────────────────
        $user->name           = $data['name'];
        $user->email          = $data['email'];
        $user->user_id_number = $data['user_id_number'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // ── Sync roles ─────────────────────────────────────────────────────────
        $user->roles()->sync($data['roles'] ?? []);

        // ── Teacher: assign to selected offerings in the active semester ───────
        // Only unassigned offerings or offerings already belonging to this teacher
        // will be updated. Offerings belonging to other teachers are skipped.
        if ($user->hasRole('teacher') && ! empty($data['offering_ids'])) {
            $activeSemester = Semester::getActive();

            if ($activeSemester) {
                // First, clear this teacher from all their current offerings this semester
                CourseOffering::where('semester_id', $activeSemester->id)
                    ->where('teacher_id', $user->id)
                    ->update(['teacher_id' => null]);

                // Then assign them to the newly selected ones (only if unowned)
                CourseOffering::where('semester_id', $activeSemester->id)
                    ->whereIn('id', $data['offering_ids'])
                    ->where(fn($q) => $q->whereNull('teacher_id')->orWhere('teacher_id', $user->id))
                    ->update(['teacher_id' => $user->id]);
            }
        }

        // ── Student: sync enrollments for active semester ──────────────────────
        if ($user->hasRole('student')) {
            $activeSemester  = Semester::getActive();
            $defaultStatusId = $data['student_status_id']
                ?? StudentStatus::where('name', 'regular')->value('id');

            if ($activeSemester && $defaultStatusId) {
                $selectedIds = $data['enroll_offering_ids'] ?? [];

                // Remove enrollments for offerings no longer selected
                Enrollment::where('student_id', $user->id)
                    ->whereHas('offering', fn($q) => $q->where('semester_id', $activeSemester->id))
                    ->whereNotIn('offering_id', $selectedIds)
                    ->delete();

                // Add new enrollments (skip duplicates)
                foreach ($selectedIds as $offeringId) {
                    Enrollment::firstOrCreate(
                        ['offering_id' => $offeringId, 'student_id' => $user->id],
                        ['student_status_id' => $defaultStatusId]
                    );
                }
            }
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account.');
        }

        // Detach roles
        $user->roles()->detach();

        // Soft-delete cascades naturally via DB constraints,
        // but we explicitly clean up active relationships first.
        if ($user->hasRole('teacher')) {
            // Unassign from all course offerings
            CourseOffering::where('teacher_id', $user->id)
                ->update(['teacher_id' => null]);
        }

        if ($user->hasRole('student')) {
            // Remove enrollments
            Enrollment::where('student_id', $user->id)->delete();
        }

        $user->delete(); // soft delete

        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }
}