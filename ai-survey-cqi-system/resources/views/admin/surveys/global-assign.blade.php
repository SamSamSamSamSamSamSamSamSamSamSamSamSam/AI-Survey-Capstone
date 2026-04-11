@extends('admin.layouts.app')
@section('title', 'Global Survey Assignment')

@section('content')
<div class="page-header">
    <h1>Global Survey Assignment</h1>
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">← Back to Surveys</a>
</div>

@if (! $activeSemester)
    <div class="alert" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;">
        No active semester is set. Please activate a semester before assigning surveys globally.
    </div>
@else

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        {{-- Form --}}
        <div class="card">
            <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">
                Configure &amp; Launch
            </div>
            <div class="card-body">

                @if (! $officialTemplate)
                    <div class="alert" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:7px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;">
                        No official template found. Please create and mark a template as the official questionnaire first.
                        <a href="{{ route('admin.survey-templates.create') }}" style="font-weight:600;">Create Template →</a>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.surveys.global-assign.store') }}">
                    @csrf

                    {{-- Template info (read-only) --}}
                    <div class="form-group">
                        <label class="form-label">Template</label>
                        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:.6rem .85rem;font-size:.875rem;">
                            @if ($officialTemplate)
                                ⭐ <strong>{{ $officialTemplate->name }}</strong>
                                <span style="color:#6b7280;font-size:.8rem;">({{ $officialTemplate->questions_count ?? '—' }} questions)</span>
                            @else
                                <span style="color:#9ca3af;">No official template set</span>
                            @endif
                        </div>
                        <p class="form-text">Always uses the official university questionnaire template.</p>
                    </div>

                    {{-- Target role --}}
                    <div class="form-group">
                        <label class="form-label">Target Role <span style="color:#dc2626">*</span></label>
                        <select name="target_role_id" class="form-control {{ $errors->has('target_role_id') ? 'is-invalid' : '' }}">
                            <option value="">Who will take these surveys?</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                        @selected(old('target_role_id') == $role->id || $role->name === 'student')>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('target_role_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
                    </div>

                    {{-- Survey period --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Start Date &amp; Time <span style="color:#dc2626">*</span></label>
                            <input type="datetime-local" name="start_date"
                                   class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                   value="{{ old('start_date') }}">
                            @error('start_date') <p class="invalid-feedback">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date &amp; Time <span style="color:#dc2626">*</span></label>
                            <input type="datetime-local" name="end_date"
                                   class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                   value="{{ old('end_date') }}">
                            @error('end_date') <p class="invalid-feedback">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Skip existing --}}
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer;">
                            <input type="hidden" name="skip_existing" value="0">
                            <input type="checkbox" name="skip_existing" value="1" checked>
                            <span>Skip offerings that already have a survey</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary"
                            @disabled(! $officialTemplate)
                            onclick="return confirm('Create surveys for all {{ $offeringsWithoutSurvey }} offering(s)? This will activate them immediately.')">
                        🚀 Assign Surveys to All Offerings
                    </button>
                </form>
            </div>
        </div>

        {{-- Summary panel --}}
        <div>
            <div class="card" style="margin-bottom:1rem;">
                <div style="padding:.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:.875rem;">
                    Active Semester Summary
                </div>
                <div class="card-body">
                    <table style="font-size:.875rem;width:100%;">
                        <tr>
                            <td style="color:#6b7280;padding:.3rem 0;">Semester</td>
                            <td><strong>{{ $activeSemester->full_label }}</strong></td>
                        </tr>
                        <tr>
                            <td style="color:#6b7280;padding:.3rem 0;">Offerings without survey</td>
                            <td>
                                <span style="font-size:1.5rem;font-weight:700;color:#4f46e5;">{{ $offeringsWithoutSurvey }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="alert alert-info" style="font-size:.85rem;line-height:1.6;">
                <strong>How this works:</strong><br>
                • One survey is created per course offering using the official template.<br>
                • Questions are copied from the template into each survey.<br>
                • All surveys are immediately <strong>activated</strong> with the period you set.<br>
                • Surveys auto-deactivate when the end date passes (scheduled command).<br>
                • When deactivated, analytics computation is triggered automatically.
            </div>
        </div>

    </div>

@endif
@endsection
