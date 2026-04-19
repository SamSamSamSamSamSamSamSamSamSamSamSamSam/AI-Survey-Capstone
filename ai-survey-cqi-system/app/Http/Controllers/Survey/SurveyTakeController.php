<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveyTakeController extends Controller
{
    // -------------------------------------------------------------------------
    // Survey list — pending / completed
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $user = Auth::user();
        $user->load('roles');

        // Use the shared helper so the same eligibility logic applies everywhere
        $surveys     = $this->getPendingSurveys($user);
        $attemptedIds = SurveyAttempt::where('student_id', $user->id)
                                     ->whereNotNull('submitted_at')
                                     ->pluck('survey_id')
                                     ->toArray();

        return view('survey.index', compact('surveys', 'attemptedIds', 'user'));
    }

    // -------------------------------------------------------------------------
    // Show survey form
    // -------------------------------------------------------------------------

    public function take(Survey $survey): View|RedirectResponse
    {
        $user = Auth::user();
        $user->load('roles');

        // ── CHANGED: use new eligibility check instead of isTargetedAt() ──
        if (! $this->isEligible($user, $survey)) {
            abort(403, 'This survey is not available for you.');
        }

        if (! $survey->is_active) {
            return redirect()->route('survey.index')
                             ->with('error', 'This survey is not currently active.');
        }

        if ($survey->hasBeenAttemptedBy($user->id)) {
            return redirect()->route('survey.index')
                             ->with('error', 'You have already submitted this survey.');
        }

        $survey->load([
            'offering.subject', 'offering.semester', 'offering.teacher',
            'questions' => fn ($q) => $q->with(['category', 'scale.options'])->orderBy('order_number'),
        ]);

        return view('survey.take', compact('survey'));
    }

    // -------------------------------------------------------------------------
    // Submit — unchanged from your version, kept exactly as teammates wrote it
    // -------------------------------------------------------------------------

    public function submit(Request $request, Survey $survey): RedirectResponse
    {
        $user = Auth::user();
        $user->load('roles');

        // ── CHANGED: use new eligibility check ──
        if (! $this->isEligible($user, $survey) || ! $survey->is_active) {
            abort(403);
        }

        if ($survey->hasBeenAttemptedBy($user->id)) {
            return redirect()->route('survey.index')
                             ->with('error', 'You have already submitted this survey.');
        }

        // Build dynamic validation rules per question — unchanged
        $survey->loadMissing('questions');
        $rules = [];

        foreach ($survey->questions as $question) {
            $key = "responses.{$question->id}";
            if ($question->isRating()) {
                $min = $question->scale?->min_value ?? 1;
                $max = $question->scale?->max_value ?? 5;
                $rules[$key] = ['required', 'integer', "min:{$min}", "max:{$max}"];
            } else {
                $rules[$key] = ['nullable', 'string', 'max:2000'];
            }
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $survey, $user) {
            $attempt = SurveyAttempt::create([
                'survey_id'  => $survey->id,
                'student_id' => $user->id,
            ]);

            foreach ($survey->questions as $question) {
                $value = $request->input("responses.{$question->id}");

                if ($value === null) continue;

                $attempt->responses()->create([
                    'survey_question_id' => $question->id,
                    'scale_value'        => $question->isRating() ? (int) $value : null,
                    'text_response'      => $question->isText()   ? $value        : null,
                ]);
            }

            $attempt->submit();

            \App\Jobs\AnalyzeSentimentJob::dispatch($attempt->id);
        });

        return redirect()->route('survey.index')
                         ->with('success', 'Your response has been submitted. Thank you!');
    }

    // =========================================================================
    // NEW METHODS — added for the target role logic change
    // =========================================================================

    /**
     * Core eligibility check. Called by take(), submit(), and getPendingSurveys().
     *
     * student → must be enrolled in the offering
     * faculty → must have faculty role AND must NOT be the teacher of this offering
     * admin   → must have admin role
     */
    public function isEligible(\App\Models\User $user, Survey $survey): bool
    {
        if (! $survey->isLive()) {
            return false;
        }

        $targetRole = $survey->targetRole?->name ?? null;

        return match ($targetRole) {
            'student' => $this->checkStudent($user, $survey),
            'faculty' => $this->checkFaculty($user, $survey),
            'admin'   => $user->hasRole('admin'),
            default   => false,
        };
    }

    /**
     * Student: must be enrolled in the specific offering.
     */
    private function checkStudent(\App\Models\User $user, Survey $survey): bool
    {
        if (! $user->hasRole('student')) {
            return false;
        }

        return \App\Models\Enrollment::where('offering_id', $survey->offering_id)
            ->where('student_id', $user->id)
            ->exists();
    }

    /**
     * Faculty: must have faculty role AND must NOT be the teacher of this offering.
     * A faculty member can evaluate any offering they do not teach.
     */
    private function checkFaculty(\App\Models\User $user, Survey $survey): bool
    {
        if (! $user->hasRole('faculty')) {
            return false;
        }

        $survey->loadMissing('offering');

        // Exclude the teacher of this specific offering only
        if ($survey->offering && $survey->offering->teacher_id === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Get all pending surveys for a user — used by both dashboards and index.
     * Single source of truth for eligibility filtering.
     */
    public function getPendingSurveys(\App\Models\User $user): \Illuminate\Database\Eloquent\Collection
    {
        $role = $user->primaryRole();

        $base = Survey::with([
                'offering.subject',
                'offering.teacher',
                'offering.semester',
                'targetRole',
                'template',
            ])
            ->withCount('questions')
            ->active()
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->whereDoesntHave('attempts', fn ($q) =>
                $q->where('student_id', $user->id)->whereNotNull('submitted_at')
            );

        return match ($role) {

            // Students: surveys targeting students for offerings they're enrolled in
            'student' => $base
                ->whereHas('targetRole', fn ($q) => $q->where('name', 'student'))
                ->whereHas('offering.enrollments', fn ($q) =>
                    $q->where('student_id', $user->id)
                )
                ->latest()
                ->get(),

            // Faculty: surveys targeting faculty, EXCLUDING courses they teach
            'faculty' => $base
                ->whereHas('targetRole', fn ($q) => $q->where('name', 'faculty'))
                ->whereDoesntHave('offering', fn ($q) =>
                    $q->where('teacher_id', $user->id)
                )
                ->latest()
                ->get(),

            // Admin: surveys targeting admins
            'admin' => $base
                ->whereHas('targetRole', fn ($q) => $q->where('name', 'admin'))
                ->latest()
                ->get(),

            default => collect(),
        };
    }
}
