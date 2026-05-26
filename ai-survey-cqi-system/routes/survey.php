<?php

use App\Http\Controllers\Admin\GlobalSurveyController;
use App\Http\Controllers\Admin\QuestionCategoryController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\SurveyQuestionController;
use App\Http\Controllers\Admin\SurveyTemplateController;
use App\Http\Controllers\Admin\SurveyWeightController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// ADMIN group (middleware: auth, verified, role:admin)
// ---------------------------------------------------------------------------

// Global Survey Assignment
Route::get('surveys/global-assign',  [GlobalSurveyController::class, 'create'])->name('surveys.global-assign');
Route::post('surveys/global-assign', [GlobalSurveyController::class, 'store']) ->name('surveys.global-assign.store');

// Surveys
Route::resource('surveys', SurveyController::class);
// Category Weights — Surveys
Route::patch('surveys/{survey}/weights',
    [SurveyWeightController::class, 'saveSurveyWeights'])
    ->name('surveys.weights.save');
Route::post('surveys/{survey}/weights/auto-distribute',
    [SurveyWeightController::class, 'autoDistributeSurvey'])
    ->name('surveys.weights.auto-distribute');
Route::patch('surveys/{survey}/toggle-active', [SurveyController::class, 'toggleActive'])->name('surveys.toggle-active');
Route::patch('surveys/{id}/restore',           [SurveyController::class, 'restore'])->name('surveys.restore')->withTrashed();
Route::get('surveys/{survey}/attempts',        [SurveyController::class, 'attempts'])->name('surveys.attempts');

// Survey Questions (nested under survey)
Route::prefix('surveys/{survey}/questions')->name('surveys.questions.')->group(function () {
    Route::get('/create',           [SurveyQuestionController::class, 'create']) ->name('create');
    Route::post('/',                [SurveyQuestionController::class, 'store'])  ->name('store');
    Route::get('/{question}/edit',  [SurveyQuestionController::class, 'edit'])   ->name('edit');
    Route::put('/{question}',       [SurveyQuestionController::class, 'update']) ->name('update');
    Route::delete('/{question}',    [SurveyQuestionController::class, 'destroy'])->name('destroy');
    Route::post('/reorder',         [SurveyQuestionController::class, 'reorder'])->name('reorder');
});

// Survey Templates
Route::resource('survey-templates', SurveyTemplateController::class);
// Category Weights — Survey Templates
Route::patch('survey-templates/{surveyTemplate}/weights',
    [SurveyWeightController::class, 'saveTemplateWeights'])
    ->name('survey-templates.weights.save');
Route::post('survey-templates/{surveyTemplate}/weights/auto-distribute',
    [SurveyWeightController::class, 'autoDistributeTemplate'])
    ->name('survey-templates.weights.auto-distribute');
Route::prefix('survey-templates/{surveyTemplate}/questions')->name('survey-templates.questions.')->group(function () {
    Route::post('/',           [SurveyTemplateController::class, 'storeQuestion'])  ->name('store');
    Route::put('/{question}',  [SurveyTemplateController::class, 'updateQuestion']) ->name('update');
    Route::delete('/{question}',[SurveyTemplateController::class, 'destroyQuestion'])->name('destroy');
    Route::post('/reorder',    [SurveyTemplateController::class, 'reorderQuestions'])->name('reorder');
});

// Question Categories
Route::resource('question-categories', QuestionCategoryController::class)->except(['show']);
