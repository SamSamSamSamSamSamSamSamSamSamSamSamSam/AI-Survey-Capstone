<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();
        return view('admin.users', compact('users','roles'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $subjects = Subject::all();

        if ($user->hasRole('teacher')) {
            $userSubjects = $user->teachingSubjects()->get();
        } elseif ($user->hasRole('student')) {
            $userSubjects = $user->enrolledSubjects()->get();
        } else {
            $userSubjects = collect();
        }

        return view('admin.users.edit', compact('user', 'roles', 'subjects', 'userSubjects'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'subjects' => 'nullable|array',
            'subjects.*.assigned' => 'nullable',
            'subjects.*.group' => 'nullable|string|max:50',
            'subjects.*.code' => 'nullable|string|max:255|distinct',
        ]);

        // Update user info
        $user->name = $data['name'];
        $user->email = $data['email'];
        if(!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Sync roles
        $user->roles()->sync($data['roles'] ?? []);

        // Build subjects relationship
        $syncData = [];
        if (!empty($data['subjects'])) {
            foreach ($data['subjects'] as $subjectId => $subjectData) {
                $assigned = !empty($subjectData['assigned']);

                // New subject
                if (str_starts_with($subjectId, 'new_') && $assigned && !empty($subjectData['code'])) {
                    $code = Str::upper(trim($subjectData['code']));
                    $subject = Subject::firstOrCreate(
                        ['course_code' => $code],
                        ['name' => $code]
                    );
                    $syncData[$subject->id] = ['group' => $subjectData['group'] ?? null];
                    continue;
                }

                // Existing subject
                if ($assigned) {
                    $syncData[$subjectId] = ['group' => $subjectData['group'] ?? null];
                }
            }
        }

        // Sync pivot
        if ($user->hasRole('teacher')) {
            $user->teachingSubjects()->sync($syncData);
        } elseif ($user->hasRole('student')) {
            $user->enrolledSubjects()->sync($syncData);
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }



    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->roles()->detach();

        if ($user->hasRole('teacher')) {
            $user->teachingSubjects()->detach();
        } elseif ($user->hasRole('student')) {
            $user->enrolledSubjects()->detach();
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }
}
