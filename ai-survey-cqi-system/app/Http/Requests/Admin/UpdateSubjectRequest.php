class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array
    {
        $id = $this->route('subject')->id;
        return [
            'course_code' => ['required', 'string', 'max:50', "unique:subjects,course_code,{$id}"],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'units'       => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}