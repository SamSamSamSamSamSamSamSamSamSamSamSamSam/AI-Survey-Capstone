<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'user_id' => ['required', 'string'],
            'user_type' => ['required', 'string', 'in:Student,Faculty,Admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'The email address already exists.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
