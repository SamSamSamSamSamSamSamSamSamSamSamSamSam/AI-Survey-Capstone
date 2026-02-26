<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunSentimentAnalysis implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle()
    {

        Artisan::call('analyze:sentiment');

        \Log::info('Sentiment analysis job executed successfully.');
    }
}
