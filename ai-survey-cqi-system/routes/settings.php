<?php
// ============================================================
// routes/settings.php
// ADD inside the admin route group
// ============================================================

use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('settings',              [SettingsController::class, 'index'])      ->name('settings.index');
Route::get('settings/test-nlp',     [SettingsController::class, 'testNlp'])   ->name('settings.test-nlp');
Route::get('settings/test-gemini',  [SettingsController::class, 'testGemini'])->name('settings.test-gemini');
Route::get('settings/logs',         [SettingsController::class, 'logs'])       ->name('settings.logs');
Route::put('settings/{group}',      [SettingsController::class, 'update'])     ->name('settings.update');
Route::post('settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
