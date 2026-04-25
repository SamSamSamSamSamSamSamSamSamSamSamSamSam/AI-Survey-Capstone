<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseOfferingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'subject_id'   => ['required', 'exists:subjects,id'],
            'semester_id'  => ['required', 'exists:semesters,id'],
            'teacher_id'   => ['required', 'exists:users,id'],
            'group_number' => [
                'required', 'integer', 'min:1',
                // Unique combination for active offerings only
                Rule::unique('course_offerings', 'group_number')->where(function ($query) {
                    return $query->where('subject_id', $this->subject_id)
                                ->where('semester_id', $this->semester_id)
                                ->whereNull('deleted_at');
                }),
            ],
            'offering_type_id' => ['nullable', 'exists:offering_types,id'],
        ];
    }
}
