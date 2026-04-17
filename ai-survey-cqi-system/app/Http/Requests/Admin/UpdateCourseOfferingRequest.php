<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseOfferingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'subject_id'       => ['required', 'exists:subjects,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
            'teacher_id'       => ['required', 'exists:users,id'],
            'group_number'     => ['nullable', 'integer', 'min:1'],
            'offering_type_id' => ['nullable', 'exists:offering_types,id'],
        ];
    }
}
