<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Faculty\DashboardController as FacultyDashboard;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Survey\SurveyTakeController;
use App\Http\Controllers\Faculty\MyReportsController;
use App\Http\Controllers\Admin\GeminiTestController;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Admin\UserController;

// ---------------------------------------------------------------------------
// Redirect root to login
// ---------------------------------------------------------------------------
Route::get('/', fn () => redirect()->route('login'))->name('home');

// ---------------------------------------------------------------------------
// Guest-only routes
// ---------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ---------------------------------------------------------------------------
// Authenticated routes
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

     Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

     // -- Email Verification --------------------------------------------------
     Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
          ->name('verification.notice');

     Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
          ->middleware('signed')
          ->name('verification.verify');

     Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
          ->middleware('throttle:6,1')
          ->name('verification.send');

     // -- Role dashboards (also requires verified email) ----------------------
     Route::middleware('verified')->group(function () {

          // ---------------------------------------------------------------------------
          // ALL AUTHENTICATED ROLES (middleware: auth, verified)
          // Place OUTSIDE role-specific groups in routes/web.php
          // ---------------------------------------------------------------------------
          Route::middleware(['auth', 'verified'])->prefix('my-surveys')->name('survey.')->group(function () {
               Route::get('/',                 [SurveyTakeController::class, 'index']) ->name('index');
               Route::get('/{survey}/take',    [SurveyTakeController::class, 'take']) ->name('take');
               Route::post('/{survey}/submit', [SurveyTakeController::class, 'submit'])->name('submit');
          });


          Route::middleware('role:admin')
               ->prefix('admin')->name('admin.')
               ->group(function () {
                    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

                    //Gemini Test Route
                    Route::get('/gemini/test', [GeminiTestController::class, 'test'])->name('gemini.test');

                         // ↓ User management routes go here (from user-management module)
                         require __DIR__ . '/admin_users.php';
                         require __DIR__ . '/academic.php';
                         require __DIR__ . '/survey.php';
                         require __DIR__ . '/cqi.php';
          });

          Route::middleware('role:faculty')
               ->prefix('faculty')->name('faculty.')
               ->group(function () {
                    Route::get('/dashboard', [FacultyDashboard::class, 'index'])->name('dashboard');
                    
                    Route::get('my-reports',                       [MyReportsController::class, 'index'])   ->name('reports.index');
                    Route::get('my-reports/{cqiReport}',           [MyReportsController::class, 'show'])    ->name('reports.show');
                    Route::get('my-reports/{cqiReport}/download',  [MyReportsController::class, 'download'])->name('reports.download');
          });

          Route::middleware('role:student')
               ->prefix('student')->name('student.')
               ->group(function () {
                    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');

                         // ↓ Student-specific routes go here (from academic module)
                         require __DIR__ . '/academic_students.php';
                    
          });
     });
});
