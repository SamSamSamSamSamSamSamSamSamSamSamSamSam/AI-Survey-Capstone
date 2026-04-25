<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Change 'code' to 'program_code'
            'program_code' => [
                'required', 'string', 'max:50',
                Rule::unique('programs', 'program_code')->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
