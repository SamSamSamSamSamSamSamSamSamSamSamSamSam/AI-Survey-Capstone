@extends('layouts.app')
@section('title', 'Edit Survey')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.surveys.show', $survey->id) }}">{{ Str::limit($survey->title, 30) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">Edit Survey</h2>
        <p class="page-subheading">Editing <strong>{{ $survey->title }}</strong></p>
    </div>
    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Survey
    </a>
</div>

<div class="form-page-layout">
    <div class="form-card">
        <form method="POST" action="{{ route('admin.surveys.update', $survey->id) }}" novalidate>
            @csrf @method('PUT')

            {{-- Template selector added to Edit --}}
            <div class="mb-4">
                <label class="form-label" for="template_id">
                    Template
                    <span class="form-label-optional">optional</span>
                </label>
                <select name="template_id" id="template_id" class="form-select  
                        @error('template_id') is-invalid @enderror"
                        {{ isset($survey) && $survey->is_active ? 'disabled' : '' }}>
                    <option value="">— Keep current questions / No template —</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}"
                                data-name="{{ $template->name }}"
                                @selected(old('template_id', $survey->template_id ?? '') == $template->id)>
                            @if ($template->is_official) ★ @else ☆ @endif{{ $template->name }}
                        </option>
                    @endforeach
                </select>
                 @if(isset($survey) && $survey->is_active)
                    <div class="form-text text-muted">
                        <i class="bi bi-lock-fill"></i> This field cannot be changed while the survey is active.
                    </div>
                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                @else
                    <div class="form-text text-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Changing the template will replace all existing questions in this survey.
                    </div>    
                @endif
            </div>

            <hr class="form-divider">

           

            @include('admin.surveys._form')

            <div class="form-actions mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>

        </form>

        <div class="form-meta">
            <i class="bi bi-clock me-1"></i>
            Created {{ $survey->created_at->format('M d, Y h:i A') }}
            &nbsp;·&nbsp;
            Updated {{ $survey->updated_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush