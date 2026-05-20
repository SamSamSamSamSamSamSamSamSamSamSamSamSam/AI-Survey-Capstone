<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Only the display name can be changed after a semester is created.
            // academic_start_year and semester_number are immutable — they define
            // the semester's identity and changing them could break course offerings.
            'name' => [
                'required',
                'string',
                'max:50',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A display name for this semester is required.',
            'name.max'      => 'The semester name may not exceed 50 characters.',
        ];
    }
}