<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Response;

class AnalyzeSentiment extends Command
{
    protected $signature = 'analyze:sentiment {--limit= : Limit the number of responses to process.}';
    protected $description = 'Analyze sentiment for survey responses using the local DistilBERT API server.';

    const SENTIMENT_API = 'http://127.0.0.1:5000';

    public function handle()
    {
        // Check the Flask server is running before doing anything
        try {
            $health = Http::timeout(3)->get(self::SENTIMENT_API . '/health');
            if (!$health->successful()) {
                $this->error('Sentiment server is not responding. Run: start_sentiment_server.bat');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Cannot connect to sentiment server at ' . self::SENTIMENT_API);
            $this->error('Start it first by running: start_sentiment_server.bat');
            return Command::FAILURE;
        }

        $this->info('Fetching responses to analyze...');

        // Set sentiment to null for rating questions
        Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
            ->update(['sentiment_label' => null, 'sentiment_score' => null]);

        // Fetch only unanalyzed text responses
        $query = Response::whereNull('sentiment_label')
            ->whereNotNull('response')
            ->whereHas('question', fn($q) => $q->where('type', 'text'));

        if ($limit = $this->option('limit')) {
            $query = $query->take((int) $limit);
        }

        $responses = $query->get();

        if ($responses->isEmpty()) {
            $this->comment('No new responses to analyze.');
            return Command::SUCCESS;
        }

        $payload = $responses->map(fn($r) => [
            'id'   => $r->id,
            'text' => $r->response,
        ])->values()->all();

        $this->comment(sprintf('Sending %d responses to sentiment server...', count($payload)));

        try {
            $apiResponse = Http::timeout(120)->post(self::SENTIMENT_API . '/analyze', $payload);

            if (!$apiResponse->successful()) {
                $this->error('Sentiment server returned an error: ' . $apiResponse->body());
                return Command::FAILURE;
            }

            $results = $apiResponse->json();
            $updatedCount = 0;

            foreach ($results as $res) {
                $record = Response::find($res['id']);
                if (!$record) continue;

                if (isset($res['sentiment_label'], $res['sentiment_score'])) {
                    $record->update([
                        'sentiment_label' => $res['sentiment_label'],
                        'sentiment_score' => $res['sentiment_score'],
                    ]);
                    $updatedCount++;
                }
            }

            $this->info("Sentiment analysis complete — updated {$updatedCount} records successfully.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Request to sentiment server failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}