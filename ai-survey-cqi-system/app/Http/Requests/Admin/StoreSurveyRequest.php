<?php
// ---------------------------------------------------------------------------
// StoreSurveyRequest
// app/Http/Requests/Admin/StoreSurveyRequest.php
// ---------------------------------------------------------------------------
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }

    public function rules(): array
    {
        return [
            'offering_id'    => ['required', 'exists:course_offerings,id'],
            'target_role_id' => ['required', 'exists:roles,id'],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
        ];
    }
}
