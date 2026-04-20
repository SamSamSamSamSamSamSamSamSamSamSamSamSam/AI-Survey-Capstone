@component('mail::message')

# Thank You for Submitting

Hi {{ $notifiable->name }},

Your survey response has been successfully recorded. We appreciate you taking the time to share your feedback.

@component('mail::panel')
**Survey:** {{ $survey->title }}
@if ($subject)
**Course:** {{ $subject->course_code }} — {{ $subject->name }}
@endif
@if ($semester)
**Semester:** {{ $semester->full_label }}
@endif
**Submitted:** {{ $attempt->submitted_at?->format(setting('locale.date_format', 'M d, Y') . ' ' . setting('locale.time_format', 'h:i A')) }}
@endcomponent

Your feedback is valuable and contributes to the continuous quality improvement of our programs.

@component('mail::button', ['url' => route('survey.index'), 'color' => 'primary'])
View My Surveys
@endcomponent

Thank you,
**{{ $appName }}**

@component('mail::subcopy')
You are receiving this email because you opted in to email notifications when submitting your survey.
To stop receiving these, simply leave the email notification toggle off on your next submission.
@endcomponent

@endcomponent
