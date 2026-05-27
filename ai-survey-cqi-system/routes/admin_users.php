<?php

// ---------------------------------------------------------------------------
// ADD these routes inside the existing admin route group in routes/web.php
// i.e. inside: Route::middleware('role:admin')->prefix('admin')->name('admin.')
// ---------------------------------------------------------------------------

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserImportController;
use Illuminate\Support\Facades\Route;


Route::get('users/import', [UserImportController::class, 'showImportForm'])->name('users.import');
Route::post('users/import', [UserImportController::class, 'import'])->name('users.import.post');
Route::post('users/import/preview', [UserImportController::class, 'previewValidation'])->name('users.import.preview');

Route::resource('users', UserController::class);

// Standard resource routes (index, create, store, show, edit, update, destroy)
Route::resource('users', UserController::class);

// Extra non-resource routes
Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])
     ->name('users.reset-password');

Route::patch('users/{user}/restore', [UserController::class, 'restore'])
     ->name('users.restore')
     ->withTrashed(); // allows route model binding on soft-deleted records

Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
     ->name('users.force-delete')
     ->withTrashed();
