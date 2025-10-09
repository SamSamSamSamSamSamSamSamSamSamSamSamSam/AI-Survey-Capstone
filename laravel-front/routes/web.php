<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Teacher\TeacherDashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherSurveyController as TeacherSurveyController;
use App\Http\Controllers\Student\StudentDashboardController as StudentDashboardController;
use App\Http\Controllers\Student\StudentSurveyController as StudentSurveyController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/login', function () {
    return view('auth.login');
})->name('login'); // This route is now '/login'

// The root path '/' can redirect to the login page
Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/signup', function () {
    return view('auth.signup');
})->name('signup');

Route::post('/signup', [UserController::class, 'signup'])->name('signup.submit');




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

Route::middleware(['web','auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Basic Admin Pages
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/department', [DepartmentsController::class, 'index'])->name('department');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    // Surveys (Resource Routes)
    Route::resource('surveys', SurveyController::class); 

    // Custom Route (Keep this one, as it's not a standard REST action)
    Route::post('/surveys/{survey}/toggle-status', [SurveyController::class, 'toggleStatus'])->name('surveys.toggle-status'); 
});


// Teacher routes
Route::middleware(['web','auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        // Surveys
        Route::get('/surveys', [TeacherSurveyController::class, 'index'])->name('survey');
        Route::get('/surveys/{survey}', [TeacherSurveyController::class, 'show'])->name('survey_take');
        Route::post('/surveys/{survey}/submit', [TeacherSurveyController::class, 'submit'])->name('surveys.submit');

        // Reviews and Classes
        Route::get('/classes', [TeacherDashboardController::class, 'survey'])->name('classes');
        Route::get('/reviews', [TeacherDashboardController::class, 'reviews'])->name('reviews');
});



// Student routes
Route::middleware(['web','auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        // Surveys
        Route::get('/surveys', [StudentSurveyController::class, 'index'])->name('surveys');
        Route::get('/surveys/{survey}', [StudentSurveyController::class, 'show'])->name('survey_take');
        Route::post('/surveys/{survey}/submit', [StudentSurveyController::class, 'submit'])->name('surveys.submit');

        // Results
        Route::get('/results', [StudentDashboardController::class, 'results'])->name('results');
});





