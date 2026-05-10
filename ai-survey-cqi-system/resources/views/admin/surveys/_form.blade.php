{{-- TomSelect CSS --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"> --}}

@php
    // Determine if we are editing and if the survey is currently active
    $isEdit = isset($survey);
    $isLocked = $isEdit && $survey->is_active;
@endphp

{{-- Course Offering --}}
<div class="mb-4">
    <label class="form-label" for="offering_id">
        Course Offerings <span class="text-danger">*</span>
    </label>
    
    <select name="offering_id[]" id="searchable-select"
            class="form-select @error('offering_id') is-invalid @enderror"
            placeholder="Type to add courses..." 
            multiple autocomplete="off"
            {{ $isLocked ? 'disabled' : '' }}>
        @foreach ($offerings as $offering)
            {{-- Get existing role IDs for this offering as a JSON array --}}
            @php
                $assignedRoles = $existingAssignments->get($offering->id, collect())->toJson();
            @endphp
            
            <option value="{{ $offering->id }}"
                data-assigned-roles="{{ $assignedRoles }}"
                @selected(in_array($offering->id, (array) old('offering_id', $isEdit ? [$survey->offering_id] : [])))>
                {{ $offering->subject->course_code }} — {{ $offering->subject->name }}
                | {{ $offering->teacher->name }} - Group {{$offering->group_number}}
            </option>
        @endforeach
    </select>

    @if($isLocked)
        {{-- ... kept your existing hidden field logic ... --}}
    @endif

    @error('offering_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Target Role --}}
<div class="mb-4">
    <label class="form-label" for="target_role_id">
        Target Role <span class="text-danger">*</span>
    </label>
    <select name="target_role_id" id="target_role_id"
            class="form-select @error('target_role_id') is-invalid @enderror"
            {{ $isLocked ? 'disabled' : '' }}>
        <option value="">Who will take this survey?</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}"
                @selected(old('target_role_id', $survey->target_role_id ?? $studentRoleId) == $role->id)>
                {{ ucfirst($role->name) }}
            </option>
        @endforeach
    </select>

    @if($isLocked)
        <div class="form-text text-muted">
            <i class="bi bi-lock-fill"></i> Locked while survey is active.
        </div>
        <input type="hidden" name="target_role_id" value="{{ $survey->target_role_id }}">
    @endif

    @error('target_role_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Survey Title --}}
<div class="mb-4">
    <label class="form-label" for="survey_title">
        Survey Title <span class="text-danger">*</span>
    </label>
    <input type="text" name="title" id="survey_title"
           class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $survey->title ?? '') }}"
           placeholder="e.g. End-of-Semester Faculty Evaluation"
           required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Survey period --}}
<div class="row g-3 mb-4">
    <div class="col-12 pb-0 mb-n2">
        <span class="form-text text-muted">
            <i class="bi bi-info-circle-fill text-primary me-1"></i> 
            Optional: Set a start and end date to schedule this survey.
        </span>
    </div>

    <div class="col-6 mt-2"> 
        <label class="form-label" for="start_date">Start Date &amp; Time</label>
        <input type="datetime-local" name="start_date" id="start_date"
            class="form-control @error('start_date') is-invalid @enderror"
            value="{{ old('start_date', (isset($survey) && $survey->start_date) ? $survey->start_date->format('Y-m-d\TH:i') : '') }}">
        @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-6 mt-2">
        <label class="form-label" for="end_date">End Date &amp; Time</label>
        <input type="datetime-local" name="end_date" id="end_date"
            class="form-control @error('end_date') is-invalid @enderror"
            value="{{ old('end_date', (isset($survey) && $survey->end_date) ? $survey->end_date->format('Y-m-d\TH:i') : '') }}">
        @error('end_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Description --}}
<div class="mb-4">
    <label class="form-label" for="description">
        Description
        <span class="form-label-optional">optional</span>
    </label>
    <textarea name="description" id="description" rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Optional instructions for respondents…">{{ old('description', $survey->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- TomSelect JS --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script> --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Initialize TomSelect
        var offeringSelect = new TomSelect("#searchable-select", {
            plugins: ['remove_button'],
            create: false,
            persist: false,
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            }
        });

        // 2. Define the filtering logic
        var roleSelect = document.querySelector('select[name="target_role_id"]');
        
function filterOfferings() {
    if (!roleSelect) return;
    
    const selectedRoleId = parseInt(roleSelect.value);
    
    // Get all currently selected items in the dropdown
    const currentValues = offeringSelect.getValue(); 
    
    // Convert currentValues to an array of strings (TomSelect uses strings for values)
    const selectedArray = Array.isArray(currentValues) ? currentValues : [currentValues.toString()];

    Object.values(offeringSelect.options).forEach(option => {
        const assignedRoles = JSON.parse(option.assignedRoles || '[]');
        const optionIdString = option.value.toString();

        // LOGIC: Is this option already selected in the box?
        const isAlreadySelected = selectedArray.includes(optionIdString);

        // If the role matches an existing assignment AND it's not the one we have selected
        if (assignedRoles.includes(selectedRoleId) && !isAlreadySelected) {
            // Disable it so the user can't pick it
            offeringSelect.updateOption(option.value, { ...option, disabled: true });
            
            // Safety: if it somehow got selected but shouldn't be, remove it
            if (selectedArray.includes(optionIdString) && !isAlreadySelected) {
                offeringSelect.removeItem(option.value);
            }
        } else {
            // Enable the option
            offeringSelect.updateOption(option.value, { ...option, disabled: false });
        }
    });

    offeringSelect.refreshOptions(false);
}

        // 3. Set up listeners
        if (roleSelect) {
            roleSelect.addEventListener('change', filterOfferings);
            // Run once on load to catch old input or preset values
            filterOfferings();
        }

        // 4. Handle locked state
        @if(isset($isLocked) && $isLocked)
            offeringSelect.disable();
        @endif
    });
</script>