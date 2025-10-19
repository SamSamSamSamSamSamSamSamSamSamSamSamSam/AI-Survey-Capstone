<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalyzeSentiment extends Command
{
    /**
     * The console command signature.
     */
    protected $signature = 'analyze:sentiment {--limit= : Limit the number of responses to process.}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze sentiment for survey responses using the Python AI script.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        $this->info('Fetching responses to analyze...');

        $query = Response::whereNull('sentiment_label')
            ->whereNotNull('response');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $responses = $query->get();

        if ($responses->isEmpty()) {
            $this->comment('No new responses to analyze.');
            return Command::SUCCESS;
        }

       
        $inputData = $responses->map(fn($r) => [
            'id'   => $r->id,
            'text' => $r->response,
        ])->toJson();

    
        $pythonPath = (PHP_OS_FAMILY === 'Windows')
            ? base_path('myvenv/Scripts/python.exe')
            : base_path('myvenv/bin/python');

        $scriptPath = base_path('resources/python/sentiment_analyzer.py');

        $this->comment(sprintf('Analyzing %d responses via Python...', $responses->count()));


        $process = new Process(
            [$pythonPath, $scriptPath],
            null, 
            ['GEMINI_API_KEY' => $geminiApiKey] 
        );
        $process->setInput($inputData);
        $process->setTimeout(3600); 

        try {
            $process->mustRun();

            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());

            $this->info("--- Python Analysis Process Output ---");

            if ($errorOutput) {
                $this->warn("Python STDERR (Client Logs/Errors):");
                $this->warn(substr($errorOutput, 0, 1000)); 
            }
            
            $this->line("Python STDOUT (JSON Payload): " . substr($output, 0, 300) . '...');
            $this->info("------------------------------------");


            $results = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Failed to decode JSON output from Python script.');
                $this->line('Output snippet: ' . substr($output, 0, 500));
                return Command::FAILURE;
            }

            $updatedCount = 0;

            foreach ($results as $res) {
                $response = Response::find($res['id']);
                if (!$response) continue;

                if (isset($res['sentiment_label'], $res['sentiment_score'])) {
                    $response->update([
                        'sentiment_label' => $res['sentiment_label'],
                        'sentiment_score' => $res['sentiment_score'],
                    ]);
                    $updatedCount++;
                }
            }

            $this->info("Sentiment analysis complete — updated {$updatedCount} records successfully.");
            return Command::SUCCESS;

        } catch (ProcessFailedException $e) {
            $this->error('Python script execution failed.');
            $this->line('Exception: ' . $e->getMessage());
            $this->line('STDERR: ' . $process->getErrorOutput());
            $this->line('STDOUT: ' . $process->getOutput());
            return Command::FAILURE;
        }
    }
}
