<?php

namespace App\Console\Commands;

use App\Jobs\ComputeFacultyAnalyticsJob;
use App\Models\Survey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageSurveySchedulesCommand extends Command
{
    // Updated signature to reflect both actions
    protected $signature   = 'surveys:update-schedules';
    protected $description = 'Activate scheduled surveys and deactivate expired ones.';

    public function handle(): int
    {
        $now = now();

        // --- 1. ACTIVATION LOGIC ---
        // Find surveys that are currently INACTIVE but should be ACTIVE
        $toActivate = Survey::where('is_active', false)
            ->whereNotNull('start_date')
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>', $now);
            })
            ->get();

        foreach ($toActivate as $survey) {
            $survey->update(['is_active' => true]);
            $this->info("✅ Activated survey [{$survey->id}]: {$survey->title}");
            Log::info("surveys:update-schedules — activated survey {$survey->id}");
        }

        // --- 2. DEACTIVATION LOGIC ---
        // Find surveys that are currently ACTIVE but have EXPIRED
        $toDeactivate = Survey::where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now)
            ->get();

        foreach ($toDeactivate as $survey) {
            $survey->update(['is_active' => false]);

            // Trigger analytics computation immediately after survey closes
            ComputeFacultyAnalyticsJob::dispatch($survey->id);

            $this->info("❌ Deactivated survey [{$survey->id}]: {$survey->title}");
            Log::info("surveys:update-schedules — deactivated survey {$survey->id}");
        }

        if ($toActivate->isEmpty() && $toDeactivate->isEmpty()) {
            $this->comment('No survey schedule changes detected.');
        } else {
            $this->info("Summary: {$toActivate->count()} activated, {$toDeactivate->count()} deactivated.");
        }

        return self::SUCCESS;
    }
}