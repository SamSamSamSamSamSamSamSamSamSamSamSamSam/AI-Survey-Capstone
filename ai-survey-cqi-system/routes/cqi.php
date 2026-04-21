<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CqiReportController;
use \App\Http\Controllers\CqiSseController;
use App\Http\Controllers\Admin\AnalyticsViewController as AdminAnalyticsView;
use Illuminate\Support\Facades\Route;


// ---------------------------------------------------------------------------
// ADMIN group (middleware: auth, verified, role:admin).
// ---------------------------------------------------------------------------

// Faculty Analytics
Route::get('analytics',                     [AnalyticsController::class, 'index'])    ->name('analytics.index');
Route::get('analytics/charts',              [AdminAnalyticsView::class, 'index'])->name('analytics.charts');
Route::get('analytics/{analytic}',          [AnalyticsController::class, 'show'])     ->name('analytics.show');
Route::post('analytics/{survey}/recompute', [AnalyticsController::class, 'recompute'])->name('analytics.recompute');
Route::patch('analytics/{id}/restore',      [AnalyticsController::class, 'restore'])       ->name('analytics.restore');
Route::resource('analytics', AnalyticsController::class)->only(['index', 'show', 'destroy']);


// CQI Reports
Route::get('cqi-reports',                   [CqiReportController::class, 'index'])         ->name('cqi-reports.index');
Route::get('cqi-reports/{cqiReport}',       [CqiReportController::class, 'show'])          ->name('cqi-reports.show');
Route::post('cqi-reports/generate',         [CqiReportController::class, 'generate'])      ->name('cqi-reports.generate');
Route::get('cqi-reports/{cqiReport}/download', [CqiReportController::class, 'download'])   ->name('cqi-reports.download');
Route::post('cqi-reports/{cqiReport}/send-to-faculty', [CqiReportController::class, 'sendToFaculty'])->name('cqi-reports.send-to-faculty');
Route::delete('cqi-reports/{cqiReport}',    [CqiReportController::class, 'destroy'])       ->name('cqi-reports.destroy');
Route::get('cqi-reports/sse/{survey_id}',   [CqiSseController::class, 'stream'])           ->name('cqi-reports.sse');

