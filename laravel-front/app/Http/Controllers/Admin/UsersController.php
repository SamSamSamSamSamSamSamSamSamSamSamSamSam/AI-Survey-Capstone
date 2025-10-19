<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // sync roles if provided; otherwise detach all roles
        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }
}