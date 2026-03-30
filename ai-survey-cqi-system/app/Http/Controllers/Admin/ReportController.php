<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\CQIDataService;
use App\Services\GeminiCQIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function __construct(
        private CQIDataService  $dataService,
        private GeminiCQIService $geminiService
    ) {}

    /**
     * Generate the CQI PDF report for a given survey and stream it as a download.
     */
    public function generateCQIReport(Request $request, ?int $surveyId = null)
    {
        // ── 1. Resolve the survey ─────────────────────────────────────────────
        $id = $surveyId ?? $request->input('survey_id');

        if (!$id) {
            return redirect()->route('admin.reports.filter')
                ->with('error', 'Please select a survey to generate a report.');
        }

        $survey = Survey::with(['evaluatee', 'subject', 'semester', 'questions.responses'])
            ->findOrFail($id);

        // ── 2. Aggregate analytics from raw responses ─────────────────────────
        $analytics = $this->dataService->build($survey);

        // ── 3. Send to Gemini and get CQI narrative ───────────────────────────
        $narrative = $this->geminiService->generate($analytics);

        // ── Debugging: log and console output ─────────────────────────────────
        Log::info('Gemini narrative generated', ['narrative' => $narrative]);

        if (app()->runningInConsole()) {
            echo "\n=== Gemini Narrative Output ===\n";
            print_r($narrative);
            echo "\n===============================\n";
        }

        // ── 4. Merge everything for the PDF view ──────────────────────────────
        $reportData = array_merge($analytics, ['narrative' => $narrative]);

        // ── 5. Render Blade → DomPDF → stream as download ─────────────────────
        $pdf = Pdf::loadView('admin.reports.pdf.cqi_report', $reportData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
                'margin_top'           => '20mm',
                'margin_bottom'        => '20mm',
                'margin_left'          => '20mm',
                'margin_right'         => '20mm',
            ]);

        $teacherName = str_replace(' ', '_', $survey->evaluatee?->name ?? 'report');
        $filename    = "CQI_Report_{$teacherName}_{$survey->semester?->academic_year}.pdf";

        return $pdf->download($filename);
    }
}