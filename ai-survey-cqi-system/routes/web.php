<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\SubjectsController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Teacher\TeacherDashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherSurveyController as TeacherSurveyController;
use App\Http\Controllers\Student\StudentDashboardController as StudentDashboardController;
use App\Http\Controllers\Student\StudentSurveyController as StudentSurveyController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Admin\CQIFilterController;
use App\Http\Controllers\Admin\SettingsController;

use App\Http\Controllers\Admin\GeminiTestController;


// // Auth routes

// Route::get('/', function () {
//     return redirect()->route('login');
// });

// // Account Setup page
// Route::get('/set-account/{user}', [SetAccountController::class, 'showSetAccountForm'])->name('auth.set-account')->middleware('signed');

// // Acccount Setup page: save password
// Route::post('/set-account/save/{email}', [SetAccountController::class, 'savePassword'])->name('auth.save-password');


// Route::middleware(['guest'])->group(function () {
    //     Route::get('/login', [AuthenticateController::class, 'login'])->name('login');
    //     Route::post('/login', [AuthenticateController::class, 'loginRequest'])->name('login.request');
// });

// Route::middleware(['auth', 'isAdmin'])->group(function () {
    
//     //Admin dashboard
//     Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

//     //User Management
//     Route::get('management/users', [UserController::class, 'userManagement'])->name('management.users');
//     Route::post('management/users/store', [UserController::class, 'storeUser'])->name('management.add.users');
//     Route::delete('management/users/delete/{id}', [UserController::class, 'destroy'])->name('management.delete.user');
// });


// Route::middleware(['auth', 'isFaculty'])->group(function () {
    
// });


// Route::middleware(['auth', 'limit.access'])->group(function () {
    
//     //user dashboard
//     Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');

//     //logout
//     Route::post('/logout', [AuthenticateController::class, 'logout'])->name('logout');

// });

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

// Placeholder for teacher reviews page -------------------------------------------------------
Route::get('/teacher/reviews', function () {
    // return view('teacher.reviews');
    return "Teacher Reviews Page - Under Construction - line 67 in web.php";
})->name('teacher.reviews')->middleware(['auth', 'role:teacher']);

Route::get('/student/dashboard', function () {
    return view('student.dashboard');
})->name('student.dashboard')->middleware(['auth', 'role:student']);

// Admin routes (only accessible to users with 'admin' role, protected by auth middleware)
Route::middleware(['web','auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard & Analysis
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analysis/surveys', [DashboardController::class, 'questionAnalysisList'])->name('analysis.surveys');
    Route::get('/analysis/questions', [DashboardController::class, 'questionAnalysis'])->name('analysis.questionAnalysis');
    Route::get('/analysis/wordcloud', [DashboardController::class, 'wordCloud'])->name('analysis.wordCloud');
    Route::get('/evaluatee/{id}', [DashboardController::class, 'evaluateeDetails'])->name('evaluatee.evaluateeDetails');
    // ── CQI Report Module ───────────────────────────────────────────────────────
    Route::get('/cqi/filter', [CQIFilterController::class, 'index'])->name('reports.filter');
    Route::get('/reports/pdf/cqi_report/{surveyId?}', [ReportController::class, 'generateCQIReport'])->name('reports.pdf.cqi_report');
    Route::post('/reports/pdf/cqi_report/generate', [ReportController::class, 'generateCQIReport'])->name('reports.pdf.cqi_report.post');
    // Route::get('/cqi/filter', function () {return view('admin.reports.filter'); })->name('reports.filter');
    // Route::get('/reports/cqi/generate/{surveyId?}', [ReportController::class, 'generateCQIReport'])->name('reports.cqi');

    //Semester
    Route::get('/semesters', [SemesterController::class, 'index'])->name('semesters.index');
    Route::post('/semesters', [SemesterController::class, 'store'])->name('semesters.store');
    Route::post('/semesters/{semester}/activate', [SemesterController::class, 'activate'])->name('semesters.activate');
    Route::delete('/semesters/{semester}', [SemesterController::class, 'destroy'])->name('semesters.destroy');

    // Users (with Subjects / Groups handled in UsersController)
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    // Departments
    Route::get('/department', [DepartmentsController::class, 'index'])->name('department');

    // Surveys
    Route::get('/surveys/use-official', [SurveyController::class, 'useOfficialQuestionnaire'])->name('surveys.useOfficial');
    Route::resource('surveys', SurveyController::class); 
    Route::post('/surveys/{survey}/toggle-status', [SurveyController::class, 'toggleStatus'])->name('surveys.toggle-status');
    Route::post('/surveys/{survey}/duplicate', [SurveyController::class, 'duplicate'])->name('surveys.duplicate');

    // Teacher-specific subjects
    Route::get('/teachers/{teacherId}/subjects', [SurveyController::class, 'getSubjectsByTeacher'])->name('teachers.subjects');

    // Subjects Management
    Route::get('/subjects', [SubjectsController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectsController::class, 'store'])->name('subjects.store');
    Route::delete('/subjects/{subject}', [SubjectsController::class, 'destroy'])->name('subjects.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/api-key', [SettingsController::class, 'clearApiKey'])->name('settings.clearKey');
    Route::get('/settings/test-key', [SettingsController::class, 'testApiKey'])->name('settings.testKey');

    //Gemini Test Route
    Route::get('/gemini/test', [GeminiTestController::class, 'test'])->name('gemini.test');

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
        // Teacher reviews page is currently a placeholder, so we won't add a route for it until it's implemented
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