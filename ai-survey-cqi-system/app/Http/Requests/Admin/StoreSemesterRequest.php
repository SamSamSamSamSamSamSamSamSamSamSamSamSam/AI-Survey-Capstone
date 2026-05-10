<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
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
                'max:3', // Usually 1, 2, and 3 (Summer/Midyear)
                // Ensures the combination of Year + Semester Number is unique
                Rule::unique('semesters')->where(function ($query) {
                    return $query->where('academic_start_year', $this->academic_start_year);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:50', // e.g., "1st Semester" or "Summer"
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'semester_number.unique' => 'This semester number already exists for the selected academic year.',
            'name.required' => 'The semester display name (e.g., 1st Semester) is required.',
        ];
    }
}