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
use Illuminate\Support\Facades\Log;

class GenerateCqiReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 5;                    // Max attempts in case of failure 
    public int $timeout = 300;                  // Max execution time (5 minutes) to allow for API calls and PDF generation

    public function backoff(): array            // Exponential backoff strategy for retries (in seconds)
    {
        return [10, 30, 60, 120, 240];
    }

    public function __construct(
        public readonly string  $surveyId,
        public readonly string  $generatedBy,   // admin user ID
        public readonly string  $scopeType,     // survey | offering | faculty
        public readonly bool    $isRegenerated = false,
    ) {}

    public function handle(GeminiService $gemini, CqiPdfService $pdf): void
    {
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

        // ------------------------------------------------------------------
        // 1. Collect open-ended response samples per question
        // ------------------------------------------------------------------
        $openEndedSamples = [];
        $textQuestions = $survey->questions->where('question_type', 'text');

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

        // ------------------------------------------------------------------
        // 2. Build data payload for Gemini
        // ------------------------------------------------------------------
        $teacher  = $survey->offering->teacher;
        $subject  = $survey->offering->subject;
        $semester = $survey->offering->semester;
        $scaleMax = $survey->questions->first()?->scale?->max_value ?? 5;

        $nameParts = explode(' ', $teacher->name);
        $lastName  = strtoupper(end($nameParts));

        $academicYear = $semester->academic_start_year . '–' . ($semester->academic_start_year + 1);

        // Ensure numeric values are explicitly cast to float/int 
        // before they get sent to the PDF service. 
        // This can help prevent issues with JSON encoding and ensure consistent formatting in the PDF.
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

        // ------------------------------------------------------------------
        // 3. Generate AI content
        // ------------------------------------------------------------------
        $aiContent = $gemini->generateCqiReport($analyticsPayload);

        // ------------------------------------------------------------------
        // 4. Build full PDF payload
        // ------------------------------------------------------------------
        $pdfPayload = array_merge($analyticsPayload, [
            'title'        => "CQI Report — {$teacher->name} — {$subject->course_code}",
            'ai_content'   => $aiContent,
            'statistics'   => $analytics->toArray(),
            'generated_at' => now()->format('F d, Y h:i A'),
        ]);

        // ------------------------------------------------------------------
        // 5. Generate PDF
        // ------------------------------------------------------------------
        $pdfPath = $pdf->generate($pdfPayload);

        // ------------------------------------------------------------------
        // 6. Save CqiReport record
        // ------------------------------------------------------------------
        $report = CqiReport::create([
            'scope_type'    => $this->scopeType,
            'survey_id'     => $survey->id,
            'offering_id'   => $survey->offering_id,
            'faculty_id'    => $teacher->id,
            'generated_by'  => $this->generatedBy,
            'title'         => "CQI Report — {$teacher->name} — {$subject->course_code} ({$semester->full_label})",
            'report_text'   => $aiContent,
            'statistics'    => array_merge($analyticsPayload, ['open_ended_samples' => $openEndedSamples]),
            'model_name'    => 'gemini',
            'model_version' => config('services.gemini.model', 'gemini-1.5-flash'),
            'pdf_path'      => $pdfPath,
            'is_regenerated'=> $this->isRegenerated,
        ]);

        // ------------------------------------------------------------------
        // 7. Log the action
        // ------------------------------------------------------------------
        CqiReportLog::create([
            'report_id'    => $report->id,
            'performed_by' => $this->generatedBy,
            'action'       => $this->isRegenerated ? 'regenerated' : 'generated',
            'notes'        => "Scope: {$this->scopeType}. Model: {$report->model_version}.",
        ]);

        Log::info("GenerateCqiReportJob: report {$report->id} generated for survey {$this->surveyId}.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateCqiReportJob failed for survey {$this->surveyId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
