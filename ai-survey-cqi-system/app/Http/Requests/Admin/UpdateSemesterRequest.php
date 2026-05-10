<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_start_year' => [
                'required',
                'integer',
                'min:2000',
                'max:2099',
            ],
            'semester_number' => [
                'required',
                'integer',
                'min:1',
                'max:3',
                // Unique check: ignore the current semester record being updated
                Rule::unique('semesters')->where(function ($query) {
                    return $query->where('academic_start_year', $this->academic_start_year);
                })->ignore($this->semester->id),
            ],
            'name' => [
                'required',
                'string',
                'max:50',
            ],
            // is_active is validated but your controller logic handles the exclusion
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'semester_number.unique' => 'This academic year already has a record for this semester number.',
        ];
    }
}