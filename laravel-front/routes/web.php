<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::post('/signup', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => 'student', // assign dynamically later if needed
    ]);

    return redirect()->route('signup')->with('success', 'Signup data submitted!');
})->name('signup.submit');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    if(Auth::attempt($validated)) {
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard'); // Redirect to admin dashboard
        } elseif ($user->role === 'faculty') {
            return redirect()->route('faculty.dashboard'); // Redirect to faculty dashboard
        } else {
            return redirect()->route('student.dashboard');  // Redirect to student dashboard
        }      

    }

    // Here you would typically check the credentials and log the user in
    return back()->with('error', 'Invalid credentials. Please try again.');
})->name('login.submit');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard')->middleware(['auth', 'role:admin']);

Route::get('/faculty/dashboard', function () {
    return view('faculty.dashboard');
})->name('faculty.dashboard')->middleware(['auth', 'role:faculty']);

Route::get('/student/dashboard', function () {
    return view('student.dashboard');
})->name('student.dashboard')->middleware(['auth', 'role:student']);

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');
