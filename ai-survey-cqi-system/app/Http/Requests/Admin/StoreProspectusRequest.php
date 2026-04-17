<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'program_id'    => ['required', 'exists:programs,id'],
            'curriculum_id' => [
                'required',
                'exists:curricula,id',
                function ($attribute, $value, $fail) {
                    $curriculum = \App\Models\Curriculum::find($value);
                    if ($curriculum && (int) $curriculum->program_id !== (int) $this->input('program_id')) {
                        $fail('The selected curriculum does not belong to the selected program.');
                    }
                },
            ],
            'subject_id'    => ['required', 'exists:subjects,id'],
            'year_level'    => ['required', 'integer', 'min:1', 'max:5'],
            'semester_id'   => ['required', 'exists:semesters,id'],  // ← was semester_number
        ];
    }

    public function messages(): array
    {
        return [
            'semester_id.required' => 'Please select a semester.',
            'semester_id.exists'   => 'The selected semester does not exist.',
        ];
    }
}
