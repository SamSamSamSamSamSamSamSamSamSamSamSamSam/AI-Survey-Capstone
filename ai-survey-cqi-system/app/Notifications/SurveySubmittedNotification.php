<?php

namespace App\Notifications;

use App\Models\SurveyAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurveySubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SurveyAttempt $attempt,
        public readonly bool $sendEmail,
        public readonly bool $sendDashboard,
    ) {}

    // -------------------------------------------------------------------------
    // Determine which channels to use based on the respondent's toggles
    // -------------------------------------------------------------------------

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        if ($this->sendDashboard) {
            $channels[] = 'database';
        }

        return $channels;
    }

    // -------------------------------------------------------------------------
    // Email channel
    // -------------------------------------------------------------------------

    public function toMail(object $notifiable): MailMessage
    {
        $survey   = $this->attempt->survey;
        $subject  = $survey->offering->subject ?? null;
        $semester = $survey->offering->semester ?? null;
        $appName  = setting('app.name', 'CQI System');

        return (new MailMessage)
            ->subject("Survey Submitted — {$appName}")
            ->markdown('emails.survey-submitted', [
                'notifiable' => $notifiable,
                'attempt'    => $this->attempt,
                'survey'     => $survey,
                'subject'    => $subject,
                'semester'   => $semester,
                'appName'    => $appName,
            ]);
    }

    // -------------------------------------------------------------------------
    // Database channel — stored in notifications table, shown on dashboard
    // -------------------------------------------------------------------------

    public function toDatabase(object $notifiable): array
    {
        $survey   = $this->attempt->survey;
        $subject  = $survey->offering->subject ?? null;
        $semester = $survey->offering->semester ?? null;

        return [
            'type'        => 'survey_submitted',
            'title'       => 'Survey Submitted',
            'message'     => "Thank you for completing the survey"
                            . ($subject ? " for {$subject->course_code}" : '')
                            . ($semester ? " ({$semester->full_label})" : '')
                            . ". Your feedback is appreciated.",
            'survey_id'   => $survey->id,
            'attempt_id'  => $this->attempt->id,
            'course_code' => $subject?->course_code,
            'semester'    => $semester?->full_label,
            'submitted_at'=> $this->attempt->submitted_at?->toISOString(),
        ];
    }
}
