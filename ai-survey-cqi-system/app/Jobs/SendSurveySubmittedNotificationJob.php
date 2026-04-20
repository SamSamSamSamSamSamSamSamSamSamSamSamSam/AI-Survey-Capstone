<?php

namespace App\Jobs;

use App\Models\SurveyAttempt;
use App\Notifications\SurveySubmittedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSurveySubmittedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly int|string $attemptId,
        public readonly bool $sendEmail,
        public readonly bool $sendDashboard,
    ) {}

    public function handle(): void
    {
        // Skip entirely if both toggles are off — should not reach here
        // but guard anyway
        if (! $this->sendEmail && ! $this->sendDashboard) {
            Log::info("SendSurveySubmittedNotificationJob: both channels off, skipping attempt {$this->attemptId}.");
            return;
        }

        $attempt = SurveyAttempt::with([
            'student',
            'survey.offering.subject',
            'survey.offering.semester',
        ])->find($this->attemptId);
        

        if (! $attempt) {
            Log::warning("SendSurveySubmittedNotificationJob: attempt {$this->attemptId} not found.");
            return;
        }

        $respondent = $attempt->student; // student_id stores any respondent

        if (! $respondent) {
            throw new \RuntimeException(
                "SendSurveySubmittedNotificationJob: respondent (student_id={$attempt->student_id}) not found for attempt [{$this->attemptId}]. " .
                "Make sure SurveyAttempt has a student() belongsTo(User::class, 'student_id') relationship."
            );
        }

        Log::info("SendSurveySubmittedNotificationJob: notifying user [{$respondent->id}] — " .
                  "email={$this->sendEmail} dashboard={$this->sendDashboard}");

        $respondent->notify(new SurveySubmittedNotification(
            attempt:       $attempt,
            sendEmail:     $this->sendEmail,
            sendDashboard: $this->sendDashboard,
        ));
        Log::info("SendSurveySubmittedNotificationJob: notification sent for attempt [{$this->attemptId}].");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendSurveySubmittedNotificationJob failed for attempt {$this->attemptId}: {$e->getMessage()}");
    }
}
