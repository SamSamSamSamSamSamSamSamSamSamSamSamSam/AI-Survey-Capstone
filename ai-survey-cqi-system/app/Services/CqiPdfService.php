<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CqiPdfService
{
    /**
     * Generate the CQI report PDF and return the storage path.
     *
     * @param  array  $reportData  Combined analytics + AI content
     * @return string  The storage path (relative to storage/app/public)
     */
    public function generate(array $reportData): string
    {
        $filename   = $this->buildFilename($reportData);
        $outputPath = storage_path("app/public/cqi-reports/{$filename}");

        // Ensure directory exists
        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Write the data payload to a temp JSON file
        $tmpJson = tempnam(sys_get_temp_dir(), 'cqi_') . '.json';
        file_put_contents($tmpJson, json_encode($reportData, JSON_UNESCAPED_UNICODE));

        // Call the Python PDF generator
        $pythonScript = base_path('resources/python/cqi_pdf_generator.py');
        $venvPython   = base_path('resources/python/myenv/bin/python');
        $python       = file_exists($venvPython) ? $venvPython : 'python3';

        $process = new Process([$python, $pythonScript, $tmpJson, $outputPath]);
        $process->setTimeout(60);
        $process->run();

        @unlink($tmpJson);

        if (! $process->isSuccessful()) {
            Log::error('CQI PDF generation failed', [
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
            ]);
            throw new \RuntimeException('PDF generation failed: ' . $process->getErrorOutput());
        }

        // Return the public-relative path for storage in DB
        return "cqi-reports/{$filename}";
    }

    /**
     * Build a clean, organised filename.
     * Format: CQI_{LASTNAME}_{COURSE_CODE}_{SEM}SEM_{AY}_{SCOPE}.pdf
     * Example: CQI_MONSERATE_CIS2105_1SEM_2025-2026_SURVEY.pdf
     */
    private function buildFilename(array $data): string
    {
        $lastName   = Str::upper(Str::slug($data['faculty_last_name'] ?? 'FACULTY', '_'));
        $courseCode = Str::upper(Str::replace(' ', '', $data['course_code'] ?? 'COURSE'));
        $sem        = ($data['semester_number'] ?? 1) . 'SEM';
        $ay         = $data['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);
        $ay         = Str::replace('/', '-', $ay);
        $scope      = Str::upper($data['scope_type'] ?? 'SURVEY');
        $timestamp  = now()->format('Ymd_His');

        return "CQI_{$lastName}_{$courseCode}_{$sem}_{$ay}_{$scope}_{$timestamp}.pdf";
    }
}
