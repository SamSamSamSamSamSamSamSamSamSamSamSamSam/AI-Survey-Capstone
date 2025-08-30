<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;

Route::get('/signup', function () {
    return view('auth.signup');
})->name('signup');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/signup', [UserController::class, 'signup'])->name('signup.submit');



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
        } elseif ($user->role === 'teacher') {
            return redirect()->route('teacher.dashboard'); // Redirect to faculty dashboard
        } else {
            return redirect()->route('student.dashboard');  // Redirect to student dashboard
        }      

    }

    // Here you would typically check the credentials and log the user in
    return back()->with('error', 'Invalid credentials. Please try again.');
})->name('login.submit');

//User Dashboards
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard')->middleware(['auth', 'role:admin']);

Route::get('/admin/users', function () {
    return view('admin.users');
})->name('admin.users')->middleware(['auth', 'role:admin']);

Route::get('/teacher/dashboard', function () {
    return view('teacher.dashboard');
})->name('teacher.dashboard')->middleware(['auth', 'role:teacher']);

Route::get('/student/dashboard', function () {
    return view('student.dashboard');
})->name('student.dashboard')->middleware(['auth', 'role:student']);

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['web','auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // basic admin pages used by the layout
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/department', [DepartmentsController::class, 'index'])->name('department');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
});

// Teacher routes
Route::middleware(['web','auth'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/classes', [TeacherDashboardController::class, 'survey'])->name('survey');
    Route::get('/reviews', [TeacherDashboardController::class, 'reviews'])->name('reviews');
});

// Student routes
Route::middleware(['web','auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/surveys', [StudentDashboardController::class, 'surveys'])->name('surveys');
    Route::get('/results', [StudentDashboardController::class, 'results'])->name('results');
});

//Survey routes



