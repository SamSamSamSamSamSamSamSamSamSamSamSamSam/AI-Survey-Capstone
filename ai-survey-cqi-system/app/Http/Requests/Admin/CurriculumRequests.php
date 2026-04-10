<?php
// ---------------------------------------------------------------------------
// StoreCurriculumRequest
// app/Http/Requests/Admin/StoreCurriculumRequest.php
// ---------------------------------------------------------------------------
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurriculumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'program_id'      => ['required', 'exists:programs,id'],
            'curriculum_code' => [
                'required', 'string', 'max:100',
                // Unique per program
                'unique:curricula,curriculum_code,NULL,id,program_id,' . $this->input('program_id'),
            ],
            'description'    => ['nullable', 'string', 'max:500'],
            'effective_year' => ['required', 'digits:4', 'integer', 'min:2000'],
            'is_active'      => ['boolean'],
        ];
    }
}

// ---------------------------------------------------------------------------
// UpdateCurriculumRequest
// app/Http/Requests/Admin/UpdateCurriculumRequest.php
// ---------------------------------------------------------------------------
class UpdateCurriculumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $id        = $this->route('curriculum')->id;
        $programId = $this->input('program_id', $this->route('curriculum')->program_id);

        return [
            'program_id'      => ['required', 'exists:programs,id'],
            'curriculum_code' => [
                'required', 'string', 'max:100',
                "unique:curricula,curriculum_code,{$id},id,program_id,{$programId}",
            ],
            'description'    => ['nullable', 'string', 'max:500'],
            'effective_year' => ['required', 'digits:4', 'integer', 'min:2000'],
            'is_active'      => ['boolean'],
        ];
    }
}

// ---------------------------------------------------------------------------
// StoreProspectusRequest  (UPDATED — curriculum_id added, offered_type_id removed)
// app/Http/Requests/Admin/StoreProspectusRequest.php
// ---------------------------------------------------------------------------
class StoreProspectusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'program_id'      => ['required', 'exists:programs,id'],
            'curriculum_id'   => [
                'required',
                'exists:curricula,id',
                // Curriculum must belong to the selected program
                function ($attribute, $value, $fail) {
                    $curriculum = \App\Models\Curriculum::find($value);
                    if ($curriculum && (int) $curriculum->program_id !== (int) $this->input('program_id')) {
                        $fail('The selected curriculum does not belong to the selected program.');
                    }
                },
            ],
            'subject_id'      => ['required', 'exists:subjects,id'],
            'year_level'      => ['required', 'integer', 'min:1', 'max:5'],
            'semester_number' => ['required', 'integer', 'in:1,2,3'],
        ];
    }
}
