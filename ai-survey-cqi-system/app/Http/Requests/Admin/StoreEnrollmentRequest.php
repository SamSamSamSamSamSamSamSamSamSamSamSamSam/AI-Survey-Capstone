<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
            'student_id' => ['required', 'array', 'min:1'],

            'student_id.*' => [
                'exists:users,id',
                'distinct', // prevent duplicates in request
            ],

            'enrollment_type_id' => ['required', 'exists:enrollment_types,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $offering = $this->route('offering');

            foreach ($this->student_id as $studentId) {

                $exists = DB::table('enrollments')
                    ->join('course_offerings', 'enrollments.offering_id', '=', 'course_offerings.id')
                    ->where('enrollments.student_id', $studentId)
                    ->where('course_offerings.subject_id', $offering->subject_id)
                    ->where('course_offerings.semester_id', $offering->semester_id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'student_id',
                        'Student is already enrolled in this subject for this semester.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'student_id.*.unique' => 'One or more selected students are already enrolled in this offering.',
            'student_id.required' => 'Please select at least one student.',
        ];
    }
}