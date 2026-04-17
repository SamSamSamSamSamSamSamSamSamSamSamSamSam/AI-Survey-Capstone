<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        $offeringId = $this->route('offering')->id;
        return [
            'student_id'        => [
                'required',
                'exists:users,id',
                // Prevent duplicate enrollment
                "unique:enrollments,student_id,NULL,id,offering_id,{$offeringId}",
            ],
            'student_status_id' => ['required', 'exists:student_statuses,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'student_id.unique' => 'This student is already enrolled in this offering.',
        ];
    }
}
