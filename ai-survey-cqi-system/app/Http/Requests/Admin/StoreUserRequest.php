<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'user_id_number' => ['required', 'string', 'max:50', 'unique:users,user_id_number'],
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'roles'          => ['required', 'array', 'min:1'],
            'roles.*'        => ['integer', 'exists:roles,id'],
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
