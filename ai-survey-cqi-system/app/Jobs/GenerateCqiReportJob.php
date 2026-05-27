<?php

namespace App\Jobs;

use App\Models\CqiReport;
use App\Models\CqiReportLog;
use App\Models\FacultyAnalytics;
use App\Models\Response;
use App\Models\Survey;
use App\Services\CqiPdfService;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateCqiReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 5;
    public int $timeout = 300;

    public function backoff(): array
    {
        return [10, 30, 60, 120, 240];
    }

    public function __construct(
        public readonly string $surveyId,
        public readonly string $generatedBy,
        public readonly string $scopeType,
        public readonly bool   $isRegenerated = false,
    ) {}

    // -------------------------------------------------------------------------
    // Status helper — writes to cache so SSE stream can broadcast it
    // -------------------------------------------------------------------------
    private function setStatus(string $status, string $message, array $extra = []): void
    {
        Cache::put("cqi_status_{$this->surveyId}", array_merge([
            'status'     => $status,
            'message'    => $message,
            'survey_id'  => $this->surveyId,
            'updated_at' => now()->toISOString(),
        ], $extra), now()->addMinutes(15));
    }

    // -------------------------------------------------------------------------
    // Strip internal weight keys from category_scores before sending to Gemini.
    //
    // ComputeFacultyAnalyticsJob stores these keys in category_scores for the
    // analytics UI, but they are implementation-level metadata — not meaningful
    // category names for Gemini to interpret. Passing them would confuse the
    // AI into treating "_weights", "_weighted_scores", etc. as real categories.
    //
    // Only the top-level raw means (e.g. "Assessment": 4.2) are sent to Gemini.
    // -------------------------------------------------------------------------
    private function cleanCategoryScores(array $categoryScores): array
    {
        return array_filter(
            $categoryScores,
            fn ($key) => ! str_starts_with((string) $key, '_'),
            ARRAY_FILTER_USE_KEY
        );
    }

    // -------------------------------------------------------------------------
    // Friendly error messages for common Gemini / PDF failures
    // -------------------------------------------------------------------------
    private function friendlyError(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'python was not found') || str_contains($lower, 'microsoft store')) {
            return 'Python is not installed or not configured correctly on this server. Install Python from python.org and ensure the virtual environment at resources/python/myenv exists.';
        }
        if (str_contains($message, '429') || str_contains($lower, 'quota') || str_contains($lower, 'resource_exhausted')) {
            return 'Gemini API quota exceeded. Please wait a few minutes and try again.';
        }
        if (str_contains($message, '503') || str_contains($lower, 'overloaded') || str_contains($lower, 'unavailable')) {
            return 'Gemini is currently overloaded or unavailable. Please retry in a few moments.';
        }
        if (str_contains($message, '401') || str_contains($message, '403') || str_contains($lower, 'api key')) {
            return 'Gemini API key is invalid or unauthorized. Check your API key in Settings → AI Configuration.';
        }
        if (str_contains($lower, 'timeout') || str_contains($lower, 'timed out')) {
            return 'The request to Gemini timed out. The service may be under load — please retry.';
        }
        if (str_contains($lower, 'invalid json') || str_contains($lower, 'json response')) {
            return 'Gemini returned an unexpected response format. This is usually temporary — please retry.';
        }
        if (str_contains($lower, 'pdf generation failed') || str_contains($lower, 'python')) {
            return 'PDF generation failed. Ensure the Python environment is correctly configured on the server.';
        }
        if (str_contains($lower, 'no query results') || str_contains($lower, 'not found')) {
            return 'Survey or analytics data not found. Please recompute analytics and try again.';
        }

        return 'An unexpected error occurred: ' . Str::limit($message, 150);
    }

    public function handle(GeminiService $gemini, CqiPdfService $pdf): void
    {
        // Step 1
        $this->setStatus('processing', 'Loading survey and analytics data…');

        $survey = Survey::with([
            'offering.subject',
            'offering.semester',
            'offering.teacher',
            'offering.teacher.roles',
            'questions.category',
            'questions.scale',
            'attempts' => fn ($q) => $q->whereNotNull('submitted_at'),
        ])->findOrFail($this->surveyId);

        $analytics = FacultyAnalytics::where('survey_id', $this->surveyId)->firstOrFail();

        // Step 2
        $this->setStatus('processing', 'Collecting open-ended responses…');

        $openEndedSamples = [];
        $textQuestions    = $survey->questions->where('question_type', 'text');

        foreach ($textQuestions as $question) {
            $responses = Response::whereHas('attempt', fn ($q) =>
                    $q->where('survey_id', $survey->id)->whereNotNull('submitted_at')
                )
                ->where('survey_question_id', $question->id)
                ->whereNotNull('text_response')
                ->pluck('text_response')
                ->filter()
                ->values()
                ->toArray();

            if (! empty($responses)) {
                $openEndedSamples[$question->question_text] = $responses;
            }
        }

        // Step 3 — Build payload
        $this->setStatus('processing', 'Preparing data payload…');

        $teacher  = $survey->offering->teacher;
        $subject  = $survey->offering->subject;
        $semester = $survey->offering->semester;
        $scaleMax = $survey->questions->first()?->scale?->max_value ?? 5;

        $nameParts    = explode(' ', $teacher->name);
        $lastName     = strtoupper(end($nameParts));
        $academicYear = $semester->academic_start_year . '–' . ($semester->academic_start_year + 1);

        // ── Strip internal weight keys from category_scores ───────────────────
        // category_scores JSON may contain _weights, _weighted_scores,
        // _achievements, _overall_weighted_score, _overall_stats from
        // ComputeFacultyAnalyticsJob. These are UI/analytics metadata and must
        // NOT be sent to Gemini as category names.
        // $rawCategoryScores     = $analytics->category_scores ?? [];
        // $cleanedCategoryScores = $this->cleanCategoryScores($rawCategoryScores);
        // ─────────────────────────────────────────────────────────────────────

        $analyticsPayload = [
            'institution'        => config('cqi.institution', 'University'),
            'department'         => config('cqi.department', ''),
            'faculty_name'       => $teacher->name,
            'faculty_last_name'  => $lastName,
            'faculty_id'         => $teacher->id,
            'course_code'        => $subject->course_code,
            'course_name'        => $subject->name,
            'program_name'       => $survey->offering->subject->name,
            'semester'           => $semester->full_label,
            'semester_number'    => $semester->semester_number,
            'academic_year'      => $academicYear,
            'group_number'       => $survey->offering->group_number ?? '—',
            'response_count'     => $analytics->response_count,
            'avg_rating'         => (float) $analytics->avg_rating,
            'scale_max'          => (int) $scaleMax,
            'positive_pct'       => (float) $analytics->positive_sentiment_percent ?? 0,
            'neutral_pct'        => $analytics->neutral_sentiment_percent  ?? 0,
            'negative_pct'       => $analytics->negative_sentiment_percent ?? 0,
            'category_scores'    => $analytics->category_scores ?? [],
            'open_ended_samples' => $openEndedSamples,
            'scope_type'         => $this->scopeType,
        ];

        // Step 4 — Gemini
        $this->setStatus('processing', 'Sending data to Gemini AI — this may take up to a minute…');

        try {
            $aiContent = $gemini->generateCqiReport($analyticsPayload);
        } catch (\Throwable $e) {
            $this->setStatus('failed', $this->friendlyError($e->getMessage()), [
                'raw_error' => $e->getMessage(),
                'step'      => 'gemini',
            ]);
            throw $e;
        }

        // Step 5 — PDF
        $this->setStatus('processing', 'Generating PDF report…');

        $pdfPayload = array_merge($analyticsPayload, [
            'title'        => "CQI Report — {$teacher->name} — {$subject->course_code}",
            'ai_content'   => $aiContent,
            'statistics'   => $analytics->toArray(), // full analytics array preserved for PDF
            'generated_at' => now()->format('F d, Y h:i A'),
        ]);

        try {
            $pdfPath = $pdf->generate($pdfPayload);
        } catch (\Throwable $e) {
            $this->setStatus('failed', $this->friendlyError($e->getMessage()), [
                'raw_error' => $e->getMessage(),
                'step'      => 'pdf',
            ]);
            throw $e;
        }

        // Step 6 — Save
        $this->setStatus('processing', 'Saving report to database…');

        $report = CqiReport::create([
            'scope_type'     => $this->scopeType,
            'survey_id'      => $survey->id,
            'offering_id'    => $survey->offering_id,
            'faculty_id'     => $teacher->id,
            'generated_by'   => $this->generatedBy,
            'title'          => "CQI Report — {$teacher->name} — {$subject->course_code} ({$semester->full_label})",
            'report_text'    => $aiContent,
            'statistics'     => array_merge($analyticsPayload, ['open_ended_samples' => $openEndedSamples]),
            'model_name'     => 'gemini',
            'model_version'  => config('services.gemini.model', 'gemini-2.5-flash'),
            'pdf_path'       => $pdfPath,
            'is_regenerated' => $this->isRegenerated,
        ]);

        CqiReportLog::create([
            'report_id'    => $report->id,
            'performed_by' => $this->generatedBy,
            'action'       => $this->isRegenerated ? 'regenerated' : 'generated',
            'notes'        => "Scope: {$this->scopeType}. Model: {$report->model_version}.",
        ]);

        // Step 7 — Done
        $this->setStatus('completed', 'CQI Report generated successfully!', [
            'report_id'    => $report->id,
            'report_title' => $report->title,
        ]);

        Log::info("GenerateCqiReportJob: report {$report->id} generated for survey {$this->surveyId}.");
    }

    public function failed(\Throwable $exception): void
    {
        $this->setStatus('failed', $this->friendlyError($exception->getMessage()), [
            'raw_error'       => $exception->getMessage(),
            'step'            => 'unknown',
            'retry_exhausted' => true,
        ]);

        Log::error("GenerateCqiReportJob failed for survey {$this->surveyId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}