<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'user_id_number' => 'required|string|unique:users,user_id_number',
            'password'       => 'required|min:6|confirmed',
            'role'           => 'required|in:student,teacher,admin',
        ]);

        $user = User::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'user_id_number' => $validated['user_id_number'],
            'password'       => Hash::make($validated['password']),
        ]);

        $role = Role::firstOrCreate(['name' => $validated['role']]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->route('login')->with('success', 'Account created. Please login.');
    }
}