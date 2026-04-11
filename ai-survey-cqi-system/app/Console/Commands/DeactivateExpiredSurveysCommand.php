<?php

namespace App\Console\Commands;

use App\Jobs\ComputeFacultyAnalyticsJob;
use App\Models\Survey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredSurveysCommand extends Command
{
    protected $signature   = 'surveys:deactivate-expired';
    protected $description = 'Deactivate surveys whose end_date has passed and trigger analytics computation.';

    public function handle(): int
    {
        $expired = Survey::where('is_active', true)
                         ->whereNotNull('end_date')
                         ->where('end_date', '<', now())
                         ->whereNull('deleted_at')
                         ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired surveys found.');
            return self::SUCCESS;
        }

        foreach ($expired as $survey) {
            $survey->update(['is_active' => false]);

            // Trigger analytics computation for each deactivated survey
            ComputeFacultyAnalyticsJob::dispatch($survey->id);

            $this->info("Deactivated survey [{$survey->id}]: {$survey->title}");
            Log::info("surveys:deactivate-expired — deactivated survey {$survey->id}");
        }

        $this->info("Total deactivated: {$expired->count()}");

        return self::SUCCESS;
    }
}
