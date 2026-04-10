<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\CQIDataService;
use App\Services\GeminiCQIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function __construct(
        private CQIDataService   $dataService,
        private GeminiCQIService $geminiService
    ) {}

    /**
     * Generate the CQI PDF report for a given survey and stream it as a download.
     *
     * Survey context (teacher, subject, semester) is now resolved through
     * the offering relationship: survey → offering → teacher/subject/semester.
     */
    public function generateCQIReport(Request $request, ?string $surveyId = null)
    {
        // ── 0. Guard: require API key ──────────────────────────────────────────
        if (! Setting::hasApiKey()) {
            return redirect()->route('admin.reports.filter')
                ->with('error', 'An AI API key is required to generate CQI reports. Please configure one in Settings.');
        }

        // ── 1. Resolve the survey ──────────────────────────────────────────────
        $id = $surveyId ?? $request->input('survey_id');

        if (! $id) {
            return redirect()->route('admin.reports.filter')
                ->with('error', 'Please select a survey to generate a report.');
        }

        // Load the full chain needed for analytics and the PDF view:
        // offering → subject, teacher, semester
        // questions → responses (via attempts) → sentiments
        $survey = Survey::with([
            'offering.subject',
            'offering.teacher',
            'offering.semester',
            'questions.responses.sentiments.sentimentType',
        ])->findOrFail($id);

        // ── 2. Aggregate analytics ─────────────────────────────────────────────
        $analytics = $this->dataService->build($survey);

        // ── 3. Generate CQI narrative via AI ──────────────────────────────────
        $narrative = $this->geminiService->generate($analytics);

        Log::info('CQI narrative generated', [
            'survey_id' => $survey->id,
            'offering'  => $survey->offering_id,
        ]);

        // ── 4. Merge for the PDF view ──────────────────────────────────────────
        $reportData = array_merge($analytics, ['narrative' => $narrative]);

        // ── 5. Render → PDF → download ─────────────────────────────────────────
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

        $teacher   = $survey->offering->teacher;
        $semester  = $survey->offering->semester;
        $startYear = $semester?->academic_start_year ?? date('Y');
        $endYear   = $startYear + 1;

        $teacherSlug = $teacher
            ? str_replace(' ', '_', $teacher->name)
            : 'report';

        $filename = "CQI_Report_{$teacherSlug}_{$startYear}-{$endYear}.pdf";

        return $pdf->download($filename);
    }
}