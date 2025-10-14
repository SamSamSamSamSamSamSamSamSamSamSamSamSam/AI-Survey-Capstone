<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class RunSentimentAnalysis implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle()
    {
        // run the existing command (will use same PHP process on queue worker)
        Artisan::call('analyze:sentiment');

         \Log::info('Sentiment analysis job executed successfully.');
    }
}