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
    public function index(): View
    {
        $user = Auth::user();
        $user->load('roles');

        $query = Survey::with(['offering.subject', 'offering.semester', 'offering.teacher', 'targetRole', 'template'])
                       ->withCount('questions')
                       ->active()
                       ->whereNull('deleted_at')
                       ->whereHas('targetRole', fn ($q) =>
                           $q->whereIn('id', $user->roles->pluck('id'))
                       );

        if ($user->hasRole('student')) {
            $query->whereHas('offering.enrollments', fn ($q) =>
                $q->where('student_id', $user->id)
            );
        } elseif ($user->hasRole('faculty')) {
            $query->whereHas('offering', fn ($q) =>
                $q->where('teacher_id', $user->id)
            );
        }

        $surveys = $query->latest()->get();

        $attemptedIds = SurveyAttempt::where('student_id', $user->id)
                                     ->whereNotNull('submitted_at')
                                     ->pluck('survey_id')
                                     ->toArray();

        return view('survey.index', compact('surveys', 'attemptedIds'));
    }

    public function take(Survey $survey): View|RedirectResponse
    {
        $user = Auth::user();
        $user->load('roles');

        if (! $survey->isTargetedAt($user)) {
            abort(403, 'This survey is not available for your role.');
        }

        if (! $survey->is_active) {
            return redirect()->route('survey.index')->with('error', 'This survey is not currently active.');
        }

        if ($survey->hasBeenAttemptedBy($user->id)) {
            return redirect()->route('survey.index')->with('error', 'You have already submitted this survey.');
        }

        $survey->load([
            'offering.subject', 'offering.semester', 'offering.teacher',
            'questions' => fn ($q) => $q->with(['category', 'scale.options'])->orderBy('order_number'),
        ]);

        return view('survey.take', compact('survey'));
    }

    public function submit(Request $request, Survey $survey): RedirectResponse
    {
        $user = Auth::user();
        $user->load('roles');

        if (! $survey->isTargetedAt($user) || ! $survey->is_active) {
            abort(403);
        }

        if ($survey->hasBeenAttemptedBy($user->id)) {
            return redirect()->route('survey.index')->with('error', 'You have already submitted this survey.');
        }

        // Build dynamic validation rules per question
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
        });

        return redirect()->route('survey.index')
                         ->with('success', 'Your response has been submitted. Thank you!');
    }
}
