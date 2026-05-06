<?php

namespace App\Jobs;

use App\Models\Response;
use App\Models\ResponseSentiment;
use App\Models\SentimentType;
use App\Models\SurveyAttempt;
use App\Services\SentimentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeSentimentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 180; // seconds

    public function __construct(
        public readonly string $attemptId
    ) {}

    public function handle(SentimentService $nlp): void
    {
        $attempt = SurveyAttempt::with([
            'responses.question',
        ])->find($this->attemptId);

        if (! $attempt) {
            Log::warning("AnalyzeSentimentJob: attempt {$this->attemptId} not found.");
            return;
        }

        // Collect only text (open-ended) responses that have content
        $textResponses = $attempt->responses->filter(
            fn ($r) => $r->question?->isText() && filled($r->text_response)
        );

        if ($textResponses->isEmpty()) {
            return;
        }

        // Build batch payload for Flask: [{id, text}, ...]
        $batch = $textResponses->map(fn ($r) => [
            'id'   => $r->id,
            'text' => $r->text_response,
        ])->values()->toArray();

        $startMs = (int) round(microtime(true) * 1000);
        $results  = $nlp->analyzeBatch($batch);
        $elapsed  = (int) round(microtime(true) * 1000) - $startMs;

        if (empty($results)) {
            Log::error("AnalyzeSentimentJob: NLP server returned empty results for attempt {$this->attemptId}");
            return;
        }

        // Index results by response ID
        $resultMap = collect($results)->keyBy('id');

        $modelName    = config('services.nlp.model_name', 'cqi-sentiment');
        $modelVersion = config('services.nlp.model_version', '1.0');

        foreach ($textResponses as $response) {
            $result = $resultMap->get($response->id);

            if (! $result || ($result['sentiment_label'] ?? '') === 'parse_error') {
                Log::warning("AnalyzeSentimentJob: skipping response {$response->id} — parse error.");
                continue;
            }

            $sentimentType = SentimentType::firstOrCreate(
                ['label' => $result['sentiment_label']]
            );

            // Use updateOrCreate so reprocessing is safe
            ResponseSentiment::updateOrCreate(
                [
                    'response_id'   => $response->id,
                    'model_version' => $modelVersion,
                ],
                [
                    'sentiment_type_id'  => $sentimentType->id,
                    'sentiment_score'    => $result['sentiment_score'],
                    'model_name'         => $modelName,
                    'model_version'      => $modelVersion,
                    'processing_time_ms' => (int) round($elapsed / count($results)),
                    'processed_at'       => now(),
                ]
            );
        }

        Log::info("AnalyzeSentimentJob: processed {$textResponses->count()} responses for attempt {$this->attemptId} in {$elapsed}ms.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzeSentimentJob failed for attempt {$this->attemptId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
