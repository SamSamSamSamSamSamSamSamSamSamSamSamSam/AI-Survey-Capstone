<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CqiSseController extends Controller
{
    public function stream(string $survey_id): StreamedResponse
    {
        return new StreamedResponse(function () use ($survey_id) {
            $cacheKey = "cqi_status_{$survey_id}";
            $maxWait  = 360; // 6 minutes max (matches job timeout + backoff)
            $elapsed  = 0;
            $interval = 2;

            // Send a heartbeat comment every tick so proxies don't drop the connection
            while ($elapsed < $maxWait) {
                $data = Cache::get($cacheKey);

                if (! $data) {
                    // Job hasn't written anything yet — still in queue
                    $data = [
                        'status'    => 'queued',
                        'message'   => 'Report is queued, waiting for a worker…',
                        'survey_id' => $survey_id,
                    ];
                }

                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();

                if (in_array($data['status'] ?? '', ['completed', 'failed'])) {
                    break;
                }

                // Heartbeat comment keeps the connection alive through proxies
                echo ": heartbeat\n\n";
                ob_flush();
                flush();

                sleep($interval);
                $elapsed += $interval;
            }

            // Timed out waiting
            if ($elapsed >= $maxWait) {
                echo "data: " . json_encode([
                    'status'    => 'failed',
                    'message'   => 'Report generation timed out. The queue worker may be down or overloaded.',
                    'raw_error' => 'SSE stream exceeded maximum wait time of ' . $maxWait . ' seconds.',
                    'step'      => 'timeout',
                ]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',   // critical for Nginx
            'Connection'        => 'keep-alive',
        ]);
    }
}