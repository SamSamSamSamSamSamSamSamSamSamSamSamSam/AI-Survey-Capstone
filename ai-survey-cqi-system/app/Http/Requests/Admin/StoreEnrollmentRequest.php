<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return $this->user()->hasRole('admin'); 
    }

    public function rules(): array
    {
        $offeringId = $this->route('offering')->id;

        return [
            // Validate that student_id is an array
            'student_id' => ['required', 'array', 'min:1'],

            // Validate each ID inside the array
            'student_id.*' => [
                'exists:users,id',
                // Unique check: student_id must be unique for this specific offering_id
                Rule::unique('enrollments', 'student_id')->where(function ($query) use ($offeringId) {
                    return $query->where('offering_id', $offeringId);
                }),
            ],

            // Note: Ensure your table name is 'enrollment_types' or 'student_statuses' 
            // Based on your controller, it was EnrollmentType
            'enrollment_type_id' => ['required', 'exists:enrollment_types,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.*.unique' => 'One or more selected students are already enrolled in this offering.',
            'student_id.required' => 'Please select at least one student.',
        ];
    }
}