<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_code' => [
                'required',
                'string',
                'max:20',
                // Allows the current subject to keep its code, but prevents others from taking it
                Rule::unique('subjects', 'course_code')->ignore($this->subject->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'units' => [
                'required',
                'integer',
                'min:1',
                'max:10', // Adjust based on your institution's max units per subject
            ],
        ];
    }

    /**
     * Custom attribute names for better error messages
     */
    public function attributes(): array
    {
        return [
            'course_code' => 'course code',
        ];
    }
}