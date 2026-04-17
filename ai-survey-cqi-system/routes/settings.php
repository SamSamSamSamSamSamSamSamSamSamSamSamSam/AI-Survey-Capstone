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


// ============================================================
// IMPORTANT: Place settings/logs and settings/test-* routes
// BEFORE settings/{group} to avoid route conflicts.
// ============================================================


// ============================================================
// composer.json — add to autoload.files to load helper globally
// ============================================================
/*
"autoload": {
    "files": [
        "app/Helpers/settings.php"
    ],
    "psr-4": {
        "App\\": "app/"
    }
}

Then run: composer dump-autoload
*/


// ============================================================
// app/Providers/AppServiceProvider.php — bind SettingService
// ============================================================
/*
public function register(): void
{
    $this->app->singleton(\App\Services\SettingService::class);
}
*/


// ============================================================
// app/Http/Middleware/MaintenanceMiddleware.php
// Register in bootstrap/app.php to block non-admins during maintenance
// ============================================================

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class MaintenanceMiddleware
// {
//     public function handle(Request $request, Closure $next)
//     {
//         $isMaintenanceMode = app(\App\Services\SettingService::class)
//             ->get('maintenance.mode', false);

//         if ($isMaintenanceMode && Auth::check() && ! Auth::user()->hasRole('admin')) {
//             $message = app(\App\Services\SettingService::class)
//                 ->get('maintenance.message', 'The system is currently under maintenance.');

//             return response()->view('errors.maintenance', compact('message'), 503);
//         }

//         return $next($request);
//     }
// }

// Register in bootstrap/app.php:
// $middleware->append(\App\Http\Middleware\MaintenanceMiddleware::class);


// ============================================================
// SIDEBAR SNIPPET — add to admin/layouts/app.blade.php
// ============================================================
/*
<p class="nav-section">System</p>
<a href="{{ route('admin.settings.index') }}"
   class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    ⚙ System Settings
</a>
*/


// ============================================================
// HOW TO USE setting() HELPER IN CODE
// ============================================================
/*
// In Blade views:
{{ setting('app.name') }}
{{ setting('app.institution', 'University') }}

// In controllers/services:
$apiKey   = setting('ai.gemini_api_key');
$timezone = setting('locale.timezone', 'UTC');

// Update GeminiService to read from settings instead of config:
$this->apiKey = setting('ai.gemini_api_key', config('services.gemini.api_key', ''));
$this->model  = setting('ai.gemini_model',   config('services.gemini.model', 'gemini-1.5-flash'));

// Update SentimentService:
$this->baseUrl = setting('ai.nlp_server_url', config('services.nlp.url', 'http://127.0.0.1:5000'));
$this->timeout = setting('ai.nlp_timeout',    config('services.nlp.timeout', 30));
*/
