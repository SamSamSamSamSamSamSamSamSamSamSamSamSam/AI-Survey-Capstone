<?php

namespace App\Http\Requests\Survey;

use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class SubmitSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Survey $survey */
        $survey = $this->route('survey');
        $survey->loadMissing('questions');

        $rules = [];

        foreach ($survey->questions as $question) {
            $key = "responses.{$question->id}";

            if ($question->isRating()) {
                // Likert scale: 1–5, required
                $rules[$key] = ['required', 'integer', 'min:1', 'max:5'];
            } else {
                // Open-ended text: optional but max length enforced
                $rules[$key] = ['nullable', 'string', 'max:2000'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $survey = $this->route('survey');
        $survey->loadMissing('questions');

        $messages = [];

        foreach ($survey->questions as $question) {
            $key = "responses.{$question->id}";
            if ($question->isRating()) {
                $messages["{$key}.required"] = "Please provide a rating for: \"{$question->question_text}\"";
                $messages["{$key}.min"]      = 'Rating must be between 1 and 5.';
                $messages["{$key}.max"]      = 'Rating must be between 1 and 5.';
            }
        }

        return $messages;
    }
}
