<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\EnrollmentController as StudentEnrollmentController;

// ---------------------------------------------------------------------------
// ADD inside the student route group (middleware: auth, verified, role:student)
// ---------------------------------------------------------------------------

Route::prefix('enrollments')->name('enrollments.')->group(function () {
    Route::get('/',              [StudentEnrollmentController::class, 'index'])  ->name('index');
    Route::post('/',             [StudentEnrollmentController::class, 'store'])  ->name('store');
    Route::delete('/{enrollment}', [StudentEnrollmentController::class, 'destroy'])->name('destroy');
});

