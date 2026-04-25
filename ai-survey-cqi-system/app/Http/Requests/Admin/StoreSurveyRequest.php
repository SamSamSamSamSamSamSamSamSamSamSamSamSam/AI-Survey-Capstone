<?php
// ---------------------------------------------------------------------------
// StoreSurveyRequest
// app/Http/Requests/Admin/StoreSurveyRequest.php
// ---------------------------------------------------------------------------
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return $this->user()->hasRole('admin'); 
    }

    public function rules(): array
    {
        return [
            'offering_id'      => ['required', 'array'],
            'offering_id.*'    => [
                'required',
                'exists:course_offerings,id',
                // This replaces the manual exists() check in your controller
                Rule::unique('surveys', 'offering_id')->where(function ($query) {
                    return $query->where('target_role_id', $this->target_role_id)
                                 ->whereNull('deleted_at');
                }),
            ],
            'target_role_id'   => ['required', 'exists:roles,id'],
            'template_id'      => ['nullable', 'exists:survey_templates,id'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'offering_id.*.unique' => 'One of the selected course offerings already has an active survey.',
            'offering_id.*.exists' => 'The selected course offering does not exist.',
        ];
    }
}