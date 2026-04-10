@extends('admin.layouts.app')
@section('title', 'Create Survey')

@section('content')
<div class="page-header">
    <h1>Create Survey</h1>
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">← Back</a>
</div>

@if (! $activeSemester)
    <div class="alert alert-info" style="max-width:680px;">No active semester set — showing all offerings.</div>
@endif

<div class="card" style="max-width:680px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.surveys.store') }}">
            @csrf

            {{-- Template selector --}}
            <div class="form-group">
                <label class="form-label">Template <span style="color:#6b7280;font-weight:400;">(optional)</span></label>
                <select name="template_id" id="template_id" class="form-control" onchange="onTemplateChange(this)">
                    <option value="">— No template / build from scratch —</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}"
                                data-name="{{ $template->name }}"
                                @selected(old('template_id') == $template->id)>
                            @if ($template->is_official) ⭐ @endif{{ $template->name }}
                        </option>
                    @endforeach
                </select>
                <p class="form-text">Selecting a template will copy its questions into this survey automatically.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Course Offering <span style="color:#dc2626">*</span></label>
                <select name="offering_id" class="form-control {{ $errors->has('offering_id') ? 'is-invalid' : '' }}">
                    <option value="">Select offering…</option>
                    @foreach ($offerings as $offering)
                        <option value="{{ $offering->id }}" @selected(old('offering_id') == $offering->id)>
                            {{ $offering->subject->course_code }} — {{ $offering->subject->name }}
                            | {{ $offering->semester->full_label }}
                            | {{ $offering->teacher->name }}
                        </option>
                    @endforeach
                </select>
                @error('offering_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Target Role <span style="color:#dc2626">*</span></label>
                <select name="target_role_id" class="form-control {{ $errors->has('target_role_id') ? 'is-invalid' : '' }}">
                    <option value="">Who will take this survey?</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('target_role_id') == $role->id)>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                @error('target_role_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Survey Title <span style="color:#dc2626">*</span></label>
                <input type="text" name="title" id="survey_title"
                       class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                       value="{{ old('title') }}"
                       placeholder="e.g. End-of-Semester Faculty Evaluation">
                @error('title') <p class="invalid-feedback">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control"
                          placeholder="Optional instructions for respondents…">{{ old('description') }}</textarea>
            </div>

            <div class="actions" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Create Survey</button>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function onTemplateChange(select) {
    const titleInput = document.getElementById('survey_title');
    const selected   = select.options[select.selectedIndex];
    if (selected.value && ! titleInput.value) {
        titleInput.value = selected.dataset.name;
    }
}
</script>
@endsection
