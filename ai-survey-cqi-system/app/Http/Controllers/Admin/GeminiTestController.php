<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class GeminiTestController extends Controller
{
    public function test()
    {
        $apiKey   = config('services.gemini.key');
        $model    = 'gemini-2.5-flash';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $result = [
            'status'        => null,   // 'ok' | 'error' | 'misconfigured'
            'http_code'     => null,
            'latency_ms'    => null,
            'model'         => $model,
            'prompt_sent'   => 'Respond with exactly: {"status":"ok","message":"Gemini is connected and working."}',
            'raw_response'  => null,
            'parsed_text'   => null,
            'error_detail'  => null,
            'key_preview'   => null,
        ];

        // ── Key check ─────────────────────────────────────────────────────────
        if (empty($apiKey)) {
            $result['status']       = 'misconfigured';
            $result['error_detail'] = 'GEMINI_API_KEY is not set in your .env file.';
            return view('admin.gemini.test', compact('result'));
        }

        $result['key_preview'] = substr($apiKey, 0, 8) . str_repeat('*', max(0, strlen($apiKey) - 8));

        // ── Fire the request ──────────────────────────────────────────────────
        $start = microtime(true);

        try {
            $response = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $result['prompt_sent']]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.1,
                        'maxOutputTokens' => 64,
                    ],
                ]);

            $result['latency_ms'] = round((microtime(true) - $start) * 1000);
            $result['http_code']  = $response->status();
            $result['raw_response'] = $response->json();

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
                $result['parsed_text'] = trim($text);
                $result['status']      = 'ok';
            } else {
                $result['status']       = 'error';
                $result['error_detail'] = data_get($response->json(), 'error.message', $response->body());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $result['status']       = 'error';
            $result['latency_ms']   = round((microtime(true) - $start) * 1000);
            $result['error_detail'] = 'Connection failed: ' . $e->getMessage();
        } catch (\Exception $e) {
            $result['status']       = 'error';
            $result['latency_ms']   = round((microtime(true) - $start) * 1000);
            $result['error_detail'] = 'Unexpected error: ' . $e->getMessage();
        }

        return view('admin.gemini.test', compact('result'));
    }
}