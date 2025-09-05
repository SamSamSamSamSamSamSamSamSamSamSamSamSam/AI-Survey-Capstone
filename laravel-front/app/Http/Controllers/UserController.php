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
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // create user (idempotent behavior not implemented here; keep simple)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // attach the 'student' role using the pivot table
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        if ($user && method_exists($user, 'roles')) {
            $user->roles()->syncWithoutDetaching([$studentRole->id]);
        }

        return redirect()->route('login')->with('success', 'Account created. Please login.');
    }
}
