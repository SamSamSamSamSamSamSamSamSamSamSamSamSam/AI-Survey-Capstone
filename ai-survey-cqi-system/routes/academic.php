<?php

use App\Http\Controllers\Admin\CourseOfferingController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ProspectusController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\SemesterSetupController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// ADD inside the admin route group (middleware: auth, verified, role:admin)
// ---------------------------------------------------------------------------

// Semester Setup Wizard
Route::get('semester-setup', [SemesterSetupController::class, 'index'])->name('semester-setup.index');
Route::post('semester-setup/preview',     [SemesterSetupController::class, 'previewValidation']) ->name('semester-setup.preview');
Route::post('semester-setup/students',   [SemesterSetupController::class, 'importStudents'])  ->name('semester-setup.import-students');
Route::post('semester-setup/blocks',     [SemesterSetupController::class, 'importBlocks'])    ->name('semester-setup.import-blocks');
Route::post('semester-setup/offerings',  [SemesterSetupController::class, 'importOfferings']) ->name('semester-setup.import-offerings');
Route::post('semester-setup/enrollments',[SemesterSetupController::class, 'importEnrollments'])->name('semester-setup.import-enrollments');

Route::resource('programs', ProgramController::class)->except(['show']);
Route::get('programs/{program}', [ProgramController::class, 'show'])->name('programs.show');
Route::patch('programs/{id}/restore', [ProgramController::class, 'restore'])
     ->name('programs.restore');

Route::resource('subjects', SubjectController::class)->except(['show']);
Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
Route::patch('subjects/{id}/restore', [SubjectController::class, 'restore'])
     ->name('subjects.restore');

Route::resource('semesters', SemesterController::class)->except(['show']);
Route::patch('semesters/{semester}/activate',   [SemesterController::class, 'activate'])  ->name('semesters.activate');
Route::patch('semesters/{semester}/deactivate', [SemesterController::class, 'deactivate'])->name('semesters.deactivate');

Route::resource('prospectus', ProspectusController::class)->only(['index', 'create', 'store', 'destroy']);

Route::resource('offerings', CourseOfferingController::class);
Route::patch('offerings/{id}/restore', [CourseOfferingController::class, 'restore'])
     ->name('offerings.restore');

// Enrollments are nested under offerings for admin
Route::prefix('offerings/{offering}/enrollments')
     ->name('offerings.enrollments.')
     ->group(function () {
         Route::get('/',        [AdminEnrollmentController::class, 'index'])  ->name('index');
         Route::get('/create',  [AdminEnrollmentController::class, 'create']) ->name('create');
         Route::post('/',       [AdminEnrollmentController::class, 'store'])  ->name('store');
         Route::delete('/{enrollment}', [AdminEnrollmentController::class, 'destroy'])->name('destroy');
     });

// Curricula resource routes
Route::resource('curricula', CurriculumController::class);

Route::patch('curricula/{curriculum}/toggle-active', [CurriculumController::class, 'toggleActive'])
     ->name('curricula.toggle-active');

Route::patch('curricula/{id}/restore', [CurriculumController::class, 'restore'])
     ->name('curricula.restore')
     ->withTrashed();

// JSON endpoint — loads curricula for a given program (used by prospectus create JS)
Route::get('curricula/by-program/{program}', function (\App\Models\Program $program) {
    $curricula = \App\Models\Curriculum::forProgram($program->id)
        ->whereNull('deleted_at')
        ->orderByDesc('effective_year')
        ->get(['id', 'curriculum_code', 'effective_year', 'is_active'])
        ->map(fn ($c) => [
            'id'            => $c->id,
            'display_label' => "{$c->curriculum_code} (Effective {$c->effective_year})",
            'is_active'     => $c->is_active,
        ]);

    return response()->json($curricula);
})->name('curricula.by-program');