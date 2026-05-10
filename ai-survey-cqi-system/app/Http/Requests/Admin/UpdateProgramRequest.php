<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set to true since you are likely handling authorization via middleware/gates
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'program_code' => [
                'required',
                'string',
                'max:20',
                // Ensures the code is unique but ignores the current record ID
                Rule::unique('programs', 'program_code')->ignore($this->program->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Custom error messages (Optional)
     */
    public function messages(): array
    {
        return [
            'program_code.unique' => 'This program code is already registered.',
            'name.required' => 'The program name is required.',
        ];
    }
}