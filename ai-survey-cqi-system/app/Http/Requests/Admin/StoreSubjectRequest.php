<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return $this->user()->hasRole('admin'); 
    }

    public function rules(): array
    {
        return [
            'course_code' => ['required', 'string', 'max:50', 'unique:subjects,course_code'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'units'       => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}