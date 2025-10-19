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
use App\Http\Controllers\OnboardingController;

// Auth routes

Route::get('/login', function () {
    return view('auth.login');
})->name('login'); 


Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/signup', function () {
    return view('auth.signup');
})->name('signup');

Route::post('/signup', [UserController::class, 'signup'])->name('signup.submit');

// Onboarding routes (shared by teacher and student)
Route::middleware(['web','auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/upload', [OnboardingController::class, 'showUploadForm'])->name('upload');
    Route::post('/upload', [OnboardingController::class, 'processUpload'])->name('process');
});




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
    Route::get('/analysis/surveys', [DashboardController::class, 'questionAnalysisList'])->name('analysis.surveys');
    Route::get('/analysis/questions', [DashboardController::class, 'questionAnalysis'])->name('analysis.questionAnalysis');
    Route::get('/analysis/wordcloud', [DashboardController::class, 'wordCloud'])->name('analysis.wordCloud');
    Route::get('/evaluatee/{id}', [DashboardController::class, 'evaluateeDetails'])->name('evaluatee.evaluateeDetails');
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::get('/department', [DepartmentsController::class, 'index'])->name('department');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');


    Route::resource('surveys', SurveyController::class); 


    Route::post('/surveys/{survey}/toggle-status', [SurveyController::class, 'toggleStatus'])->name('surveys.toggle-status');
    Route::get('/teachers/{teacherId}/subjects', [SurveyController::class, 'getSubjectsByTeacher'])->name('teachers.subjects');
});


// Teacher routes
Route::middleware(['web','auth', 'role:teacher', 'check.onboarding'])->prefix('teacher')->name('teacher.')->group(function () {
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
Route::middleware(['web','auth', 'role:student', 'check.onboarding'])->prefix('student')->name('student.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        // Surveys
        Route::get('/surveys', [StudentSurveyController::class, 'index'])->name('survey');
        Route::get('/surveys/{survey}', [StudentSurveyController::class, 'show'])->name('survey_take');
        Route::post('/surveys/{survey}/submit', [StudentSurveyController::class, 'submit'])->name('surveys.submit');

        // Results
        Route::get('/results', [StudentDashboardController::class, 'results'])->name('results');
});





