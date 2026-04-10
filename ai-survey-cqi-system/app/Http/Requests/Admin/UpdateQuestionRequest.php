<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

// ---------------------------------------------------------------------------
// UpdateQuestionRequest
// app/Http/Requests/Admin/UpdateQuestionRequest.php
// ---------------------------------------------------------------------------
class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'category'      => ['nullable', 'string', 'max:100'],
            'type'          => ['required', 'in:rating,text'],
            'order'         => ['sometimes', 'integer', 'min:1'],
        ];
    }
}