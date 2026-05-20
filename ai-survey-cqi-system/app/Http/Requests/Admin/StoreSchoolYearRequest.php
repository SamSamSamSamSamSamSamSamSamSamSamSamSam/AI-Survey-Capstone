<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolYearRequest extends FormRequest
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
            'include_summer' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_start_year.required' => 'Please provide the starting year of the school year.',
            'academic_start_year.integer'  => 'The academic start year must be a valid year.',
            'academic_start_year.min'      => 'The academic start year must be 2000 or later.',
            'academic_start_year.max'      => 'The academic start year must be 2099 or earlier.',
        ];
    }

    /**
     * Prepare the data for validation — cast the checkbox value.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_summer' => $this->boolean('include_summer'),
        ]);
    }
}