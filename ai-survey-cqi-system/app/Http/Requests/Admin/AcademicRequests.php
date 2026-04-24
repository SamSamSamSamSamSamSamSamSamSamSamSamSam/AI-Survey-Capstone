<?php
// ---------------------------------------------------------------------------
// StoreProgramRequest
// app/Http/Requests/Admin/StoreProgramRequest.php
// ---------------------------------------------------------------------------
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'program_code' => ['required', 'string', 'max:50', 'unique:programs,program_code'],
            'name'         => ['required', 'string', 'max:255'],
        ];
    }
}

// ---------------------------------------------------------------------------
// UpdateProgramRequest
// app/Http/Requests/Admin/UpdateProgramRequest.php
// ---------------------------------------------------------------------------
// namespace App\Http\Requests\Admin;
// use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        $id = $this->route('program')->id;
        return [
            'program_code' => ['required', 'string', 'max:50', "unique:programs,program_code,{$id}"],
            'name'         => ['required', 'string', 'max:255'],
        ];
    }
}

// ---------------------------------------------------------------------------
// StoreSemesterRequest
// app/Http/Requests/Admin/StoreSemesterRequest.php
// ---------------------------------------------------------------------------
class StoreSemesterRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:100'],
            'academic_start_year' => ['required', 'digits:4', 'integer', 'min:2000'],
            'semester_number'     => ['required', 'integer', 'in:1,2,3'],
        ];
    }
}

// ---------------------------------------------------------------------------
// UpdateSemesterRequest
// app/Http/Requests/Admin/UpdateSemesterRequest.php
// ---------------------------------------------------------------------------
class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:100'],
            'academic_start_year' => ['required', 'digits:4', 'integer', 'min:2000'],
            'semester_number'     => ['required', 'integer', 'in:1,2,3'],
        ];
    }
}

// ---------------------------------------------------------------------------
// StoreProspectusRequest
// app/Http/Requests/Admin/StoreProspectusRequest.php
// ---------------------------------------------------------------------------
class StoreProspectusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'program_id'      => ['required', 'exists:programs,id'],
            'subject_id'      => ['required', 'exists:subjects,id'],
            'year_level'      => ['required', 'integer', 'min:1', 'max:5'],
            'semester_number' => ['required', 'integer', 'in:1,2,3'],
            'offered_type_id' => ['nullable', 'exists:offering_types,id'],
        ];
    }
}

// ---------------------------------------------------------------------------
// StoreCourseOfferingRequest
// app/Http/Requests/Admin/StoreCourseOfferingRequest.php
// ---------------------------------------------------------------------------
class StoreCourseOfferingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'subject_id'       => ['required', 'exists:subjects,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
            'teacher_id'       => ['required', 'exists:users,id'],
            'group_number'     => ['nullable', 'integer', 'min:1'],
            'offering_type_id' => ['nullable', 'exists:offering_types,id'],
        ];
    }
}

// ---------------------------------------------------------------------------
// UpdateCourseOfferingRequest
// app/Http/Requests/Admin/UpdateCourseOfferingRequest.php
// ---------------------------------------------------------------------------
class UpdateCourseOfferingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        return [
            'subject_id'       => ['required', 'exists:subjects,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
            'teacher_id'       => ['required', 'exists:users,id'],
            'group_number'     => ['nullable', 'integer', 'min:1'],
            'offering_type_id' => ['nullable', 'exists:offering_types,id'],
        ];
    }
}

// ---------------------------------------------------------------------------
// StoreEnrollmentRequest
// app/Http/Requests/Admin/StoreEnrollmentRequest.php
// ---------------------------------------------------------------------------
class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        $offeringId = $this->route('offering')->id;
        return [
            'student_id'        => [
                'required',
                'exists:users,id',
                // Prevent duplicate enrollment
                "unique:enrollments,student_id,NULL,id,offering_id,{$offeringId}",
            ],
            'student_status_id' => ['required', 'exists:student_statuses,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'student_id.unique' => 'This student is already enrolled in this offering.',
        ];
    }
}
