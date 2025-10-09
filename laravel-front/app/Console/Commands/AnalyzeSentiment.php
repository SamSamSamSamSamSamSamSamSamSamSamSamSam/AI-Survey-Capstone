<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Response;

class AnalyzeSentiment extends Command
{
    protected $signature = 'analyze:sentiment';
    protected $description = 'Analyze sentiment for survey responses';

    public function handle()
    {
        $responses = Response::whereNull('sentiment_label')
            ->whereNotNull('response')
            ->get();

        if ($responses->isEmpty()) {
            $this->info("No responses to analyze.");
            return;
        }

        $inputData = $responses->map(fn($r) => [
            'id' => $r->id,
            'text' => $r->response
        ])->toArray();
        
        $pythonPath = base_path('myvenv/Scripts/python.exe'); // <-- full path to your venv Python
        $scriptPath = base_path('resources/python/sentiment_analyzer.py');
        // Call Python script
        $process = proc_open(
            "\"$pythonPath\" \"$scriptPath\"",
            [
                ['pipe', 'r'], // stdin
                ['pipe', 'w'], // stdout
                ['pipe', 'w']  // stderr
            ],
            $pipes
        );

        if (is_resource($process)) {
            fwrite($pipes[0], json_encode($inputData));
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            proc_close($process);

            if ($error) {
                $this->error("Python error: " . $error);
                return;
            }

            $results = json_decode($output, true);
            foreach ($results as $res) {
                $response = Response::find($res['id']);
                $response->update([
                    'sentiment_label' => $res['sentiment_label'],
                    'sentiment_score' => $res['sentiment_score'],
                ]);
            }

            $this->info("Sentiment analysis completed for {$responses->count()} responses.");
        }
    }
}
