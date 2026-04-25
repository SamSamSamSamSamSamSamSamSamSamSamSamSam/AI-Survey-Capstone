<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'user_id_number' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'user_id_number')->whereNull('deleted_at'),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'name'  => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Please assign at least one role.',
            'roles.min'      => 'Please assign at least one role.',
        ];
    }
}
