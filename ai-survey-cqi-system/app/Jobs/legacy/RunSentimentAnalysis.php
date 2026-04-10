<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunSentimentAnalysis
{
    use Dispatchable;

    public function handle()
    {
        Artisan::call('analyze:sentiment');

        Log::info('Sentiment analysis job executed successfully.');
    }
}