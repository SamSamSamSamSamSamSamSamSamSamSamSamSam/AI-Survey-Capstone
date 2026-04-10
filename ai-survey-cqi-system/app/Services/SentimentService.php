<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SentimentService
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.nlp.url', 'http://127.0.0.1:5000');
        $this->timeout = config('services.nlp.timeout', 30);
    }

    /**
     * Check if the NLP server is reachable.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->ok();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Analyze sentiment for a batch of text responses.
     *
     * Input:  [['id' => string, 'text' => string], ...]
     * Output: [['id' => string, 'sentiment_label' => string, 'sentiment_score' => float], ...]
     *
     * Flask server `/analyze` accepts: JSON array of {id, text}
     * Returns: JSON array of {id, sentiment_label, sentiment_score}
     */
    public function analyzeBatch(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/analyze", $items);

            if ($response->failed()) {
                Log::error('NLP server returned error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            return $response->json() ?? [];

        } catch (\Throwable $e) {
            Log::error('NLP server unreachable', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Analyze a single text — convenience wrapper.
     */
    public function analyzeSingle(string $id, string $text): ?array
    {
        $results = $this->analyzeBatch([['id' => $id, 'text' => $text]]);
        return $results[0] ?? null;
    }
}
