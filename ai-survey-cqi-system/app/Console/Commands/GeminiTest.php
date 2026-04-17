<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gemini:test
                            {--verbose-response : Print the full raw JSON response from Gemini}';

    /**
     * The console command description.
     */
    protected $description = 'Test the Gemini API connection and print diagnostics to the terminal';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>Gemini API Connection Test</>');
        $this->line('  ' . str_repeat('─', 44));
        $this->newLine();

        $dbValue = setting('ai.gemini_api_key');

        if ($dbValue) {
            Log::info('Gemini API Key accessed from Database.');
            $apiKey = $dbValue;
        } else {
            // 2. Fallback to config (.env)
            $apiKey = config('services.gemini.api_key', '');
            
            if (!empty($apiKey)) {
                Log::warning('Gemini API Key accessed from .env fallback (Database record missing).');
            }
        }
        \Log::emergency("THE CODE REACHED THIS POINT");

        // ── Key check ─────────────────────────────────────────────────────────
        $apiKey = setting('ai.gemini_api_key', config('services.gemini.api_key', ''));

        if (empty($apiKey)) {
            $this->error('  ✗  GEMINI_API_KEY is not set in your .env file.');
            $this->newLine();
            $this->line('  <fg=yellow>Fix:</> Add the following to your .env:');
            $this->line('       GEMINI_API_KEY=your_key_here');
            $this->line('  Then run: <fg=yellow>php artisan config:clear</>');
            $this->newLine();
            return Command::FAILURE;
        }

        $keyPreview = substr($apiKey, 0, 8) . str_repeat('*', max(0, strlen($apiKey) - 8));
        $this->line("  <fg=gray>API Key :</> {$keyPreview}");

        $model    = 'gemini-2.5-flash';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $prompt   = 'Respond with exactly this JSON and nothing else: {"status":"ok","message":"Gemini is connected and working."}';

        $this->line("  <fg=gray>Model   :</> {$model}");
        $this->line("  <fg=gray>Prompt  :</> {$prompt}");
        $this->newLine();
        $this->line('  Sending request...');

        // ── Fire request ──────────────────────────────────────────────────────
        $start = microtime(true);

        try {
            $response = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.1,
                        'maxOutputTokens' => 64,
                    ],
                ]);

            $latency = round((microtime(true) - $start) * 1000);

            $this->newLine();
            $this->line("  <fg=gray>HTTP Status :</> {$response->status()}");
            $this->line("  <fg=gray>Latency     :</> {$latency} ms");
            $this->newLine();

            // ── Success ───────────────────────────────────────────────────────
            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
                $text = trim($text);

                $this->line('  <fg=green;options=bold>✓  Connection successful!</>');
                $this->newLine();
                $this->line('  <fg=gray>Gemini responded:</> ' . $text);

                if ($this->option('verbose-response')) {
                    $this->newLine();
                    $this->line('  <fg=cyan>── Full Raw JSON ──────────────────────────────────────────</>');
                    $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
                }

                $this->newLine();
                $this->line('  <fg=green>The Gemini API is working correctly. CQI report generation is ready.</>');
                $this->newLine();
                return Command::SUCCESS;
            }

            // ── HTTP error ────────────────────────────────────────────────────
            $this->error('  ✗  Gemini returned an error response.');
            $errorMessage = data_get($response->json(), 'error.message', 'No error message returned.');
            $this->newLine();
            $this->line("  <fg=red>Error:</> {$errorMessage}");
            $this->newLine();
            $this->printTroubleshootingHint($response->status());
            $this->newLine();
            return Command::FAILURE;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $latency = round((microtime(true) - $start) * 1000);
            $this->newLine();
            $this->error("  ✗  Connection failed after {$latency} ms.");
            $this->newLine();
            $this->line('  <fg=red>Detail:</> ' . $e->getMessage());
            $this->newLine();
            $this->line('  <fg=yellow>Possible causes:</>');
            $this->line('    • Your server cannot reach external URLs (firewall/proxy)');
            $this->line('    • DNS resolution failure');
            $this->line('    • Request timed out (Gemini took longer than 20s)');
            $this->newLine();
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('  ✗  Unexpected exception: ' . $e->getMessage());
            $this->newLine();
            return Command::FAILURE;
        }
    }

    // ── Troubleshooting hints by HTTP code ────────────────────────────────────

    private function printTroubleshootingHint(int $httpCode): void
    {
        $this->line('  <fg=yellow>Troubleshooting:</>');

        match (true) {
            $httpCode === 400 => $this->line('    • HTTP 400: Bad request — check the model name in GeminiCQIService.php'),
            $httpCode === 401 => $this->line('    • HTTP 401: Unauthorized — your API key is invalid or expired'),
            $httpCode === 403 => $this->line('    • HTTP 403: Forbidden — enable the Generative Language API in Google Cloud Console'),
            $httpCode === 429 => $this->line('    • HTTP 429: Rate limit hit — wait a moment and retry (free tier: 15 req/min)'),
            $httpCode >= 500  => $this->line('    • HTTP ' . $httpCode . ': Gemini server error — try again in a few minutes'),
            default           => $this->line('    • Run: php artisan config:clear && php artisan cache:clear'),
        };

        $this->line('    • Check storage/logs/laravel.log for more detail');
        $this->line('    • Verify your key at: https://aistudio.google.com/app/apikey');
    }
}