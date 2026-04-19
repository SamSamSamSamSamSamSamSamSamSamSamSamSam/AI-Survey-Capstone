<?php

// app/Http/Controllers/CqiSseController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Cache;

class CqiSseController extends Controller
{
    public function stream($survey_id)
    {
        return new StreamedResponse(function () use ($survey_id) {
            $cacheKey = "cqi_status_{$survey_id}";

            while (true) {
                $data = Cache::get($cacheKey);
                
                // Send the data as a Server-Sent Event
                echo "data: " . json_encode($data ?? ['message' => 'Processing...', 'status' => 'processing']) . "\n\n";
                
                // If job is done, stop the stream
                if (!$data || (isset($data['status']) && $data['status'] === 'completed')) {
                    break;
                }

                ob_flush();
                flush();
                sleep(1); // Wait 1 second before checking again
            }
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache', 'Connection' => 'keep-alive']);
    }
}