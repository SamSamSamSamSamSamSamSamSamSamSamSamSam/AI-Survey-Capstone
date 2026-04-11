<?php
// ============================================================
// database/seeders/EnrollmentTypeSeeder.php
// ============================================================
namespace Database\Seeders;

use App\Models\EnrollmentType;
use Illuminate\Database\Seeder;

class EnrollmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Block-Enrolled',        'description' => 'Student enrolled as part of a block/section.'],
            ['name' => 'Non-Block-Enrolled', 'description' => 'Student enrolled individually, not in a block.'],
        ];

        foreach ($types as $type) {
            EnrollmentType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}

// ============================================================
// routes/revisions.php
// ADD these routes to routes/web.php in the correct groups
// ============================================================

/*
// ── ADMIN group ─────────────────────────────────────────────

use App\Http\Controllers\Admin\GlobalSurveyController;
use App\Http\Controllers\Admin\SemesterSetupController;

// Semester Setup Wizard
Route::get('semester-setup', [SemesterSetupController::class, 'index'])->name('semester-setup.index');
Route::post('semester-setup/students',   [SemesterSetupController::class, 'importStudents'])  ->name('semester-setup.import-students');
Route::post('semester-setup/blocks',     [SemesterSetupController::class, 'importBlocks'])    ->name('semester-setup.import-blocks');
Route::post('semester-setup/offerings',  [SemesterSetupController::class, 'importOfferings']) ->name('semester-setup.import-offerings');
Route::post('semester-setup/enrollments',[SemesterSetupController::class, 'importEnrollments'])->name('semester-setup.import-enrollments');

// Global Survey Assignment
Route::get('surveys/global-assign',  [GlobalSurveyController::class, 'create'])->name('surveys.global-assign');
Route::post('surveys/global-assign', [GlobalSurveyController::class, 'store']) ->name('surveys.global-assign.store');

// ── IMPORTANT: place global-assign routes BEFORE resource route ──────────────
// Route::resource('surveys', SurveyController::class);  ← after the above
*/

// ============================================================
// routes/console.php  (or bootstrap/app.php withSchedule)
// Register the scheduled command
// ============================================================

/*
// In bootstrap/app.php, add:
->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
    // Run every 5 minutes to catch survey expirations promptly
    $schedule->command('surveys:deactivate-expired')->everyFiveMinutes();
})

// Or in routes/console.php:
use Illuminate\Support\Facades\Schedule;
Schedule::command('surveys:deactivate-expired')->everyFiveMinutes();
*/

// ============================================================
// SIDEBAR SNIPPET
// Add to admin/layouts/app.blade.php
// ============================================================

/*
<p class="nav-section">Setup</p>
<a href="{{ route('admin.semester-setup.index') }}"
   class="nav-link {{ request()->routeIs('admin.semester-setup.*') ? 'active' : '' }}">
   Semester Setup Wizard
</a>
<a href="{{ route('admin.surveys.global-assign') }}"
   class="nav-link {{ request()->routeIs('admin.surveys.global-assign*') ? 'active' : '' }}">
   Global Survey Assignment
</a>
*/
