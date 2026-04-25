<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return $this->user()->hasRole('admin'); 
    }

    public function rules(): array
    {
        return [
            'course_code' => [
                'required', 
                'string', 
                'max:50',
                // Allow creation if the same code exists but is soft-deleted
                Rule::unique('subjects', 'course_code')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'name'        => ['required', 'string', 'max:255'],
            'units'       => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}