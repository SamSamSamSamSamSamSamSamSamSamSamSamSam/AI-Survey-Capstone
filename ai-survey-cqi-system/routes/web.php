<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Faculty\DashboardController as FacultyDashboard;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Survey\SurveyTakeController;
use App\Http\Controllers\Faculty\MyReportsController;
use App\Http\Controllers\Admin\GeminiTestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login or dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->to(auth()->user()->dashboardRoute());
    }
    return redirect()->route('login');
})->name('home');

// ---------------------------------------------------------------------------
// Account Activation & Password Reset (The "Brevo" Flow)
// ---------------------------------------------------------------------------
// These must be public so users can click the link in their email
Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
     ->name('password.reset');

Route::post('reset-password', [NewPasswordController::class, 'store'])
     ->name('password.update');

// ---------------------------------------------------------------------------
// Guest-only routes (Login)
// ---------------------------------------------------------------------------
Route::middleware('redirect.authenticated')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
});

// ---------------------------------------------------------------------------
// Authenticated routes
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    /* NOTE: We are keeping the 'verified' middleware check for dashboards.
       Imported users will bypass the old VerifyEmailController because 
       NewPasswordController marks them as verified automatically.
    */
    Route::middleware('verified')->group(function () {

        // -- Survey Participation (Shared) --
        Route::prefix('my-surveys')->name('survey.')->group(function () {
            Route::get('/',                 [SurveyTakeController::class, 'index'])->name('index');
            Route::get('/{survey}/take',    [SurveyTakeController::class, 'take'])->name('take');
            Route::post('/{survey}/submit', [SurveyTakeController::class, 'submit'])->name('submit');
        });

        // -- Admin Routes --
        Route::middleware('role:admin')
            ->prefix('admin')->name('admin.')
            ->group(function () {
                Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
                Route::get('/gemini/test', [GeminiTestController::class, 'test'])->name('gemini.test');

                // Internal Module Routes
                // IMPORTANT: Ensure 'admin.users.import' is defined inside admin_users.php
                require __DIR__ . '/admin_users.php'; 
                require __DIR__ . '/academic.php';
                require __DIR__ . '/survey.php';
                require __DIR__ . '/cqi.php';
                require __DIR__ . '/settings.php';
            });

        // -- Faculty Routes --
        Route::middleware('role:faculty')
            ->prefix('faculty')->name('faculty.')
            ->group(function () {
                Route::get('/dashboard', [FacultyDashboard::class, 'index'])->name('dashboard');
                Route::get('my-reports',                    [MyReportsController::class, 'index'])->name('reports.index');
                Route::get('my-reports/{cqiReport}',        [MyReportsController::class, 'show'])->name('reports.show');
                Route::get('my-reports/{cqiReport}/download', [MyReportsController::class, 'download'])->name('reports.download');
            });

        // -- Student Routes --
        Route::middleware('role:student')
            ->prefix('student')->name('student.')
            ->group(function () {
                Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
                require __DIR__ . '/academic_students.php';
            });
    });
});