@extends('layouts.default')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="text-center mb-4">
                <h3 class="mb-1">Set Up Your Subjects</h3>
                @if($activeSemester)
                    <p class="text-muted">
                        You're setting up for:
                        <span class="badge bg-primary fs-6 px-3 py-1 ms-1">
                            <i class="bi bi-calendar2-range me-1"></i>
                            {{ $activeSemester->name }}
                        </span>
                    </p>
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No active semester has been set by the admin yet.
                        Please check back later.
                    </div>
                @endif
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            @if($activeSemester)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-journal-text me-1"></i>
                    Enter Your Subjects
                </div>
                <div class="card-body">

                    <p class="text-muted small mb-3">
                        Add each subject you are enrolled in this semester.
                        You can add more rows if needed.
                    </p>

                    {{-- Datalist for autocomplete --}}
                    <datalist id="subjectList">
                        @foreach($existingSubjects as $s)
                            <option value="{{ $s->course_code }}">{{ $s->name }}</option>
                        @endforeach
                    </datalist>

                    <form action="{{ route('onboarding.process') }}" method="POST" id="onboardingForm">
                        @csrf

                        <div id="subjectRows">
                            {{-- Initial row --}}
                            <div class="row g-2 mb-2 subject-row">
                                <div class="col-7">
                                    <input type="text"
                                           name="subjects[0][code]"
                                           class="form-control"
                                           placeholder="Course Code (e.g. IT3201)"
                                           list="subjectList"
                                           autocomplete="off"
                                           required>
                                </div>
                                <div class="col-4">
                                    <input type="text"
                                           name="subjects[0][group]"
                                           class="form-control"
                                           placeholder="Group (e.g. 1)">
                                </div>
                                <div class="col-1 d-flex align-items-center">
                                    {{-- Remove button hidden on first row --}}
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger remove-row d-none"
                                            title="Remove">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Column labels --}}
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <small class="text-muted ms-1">Course Code</small>
                            </div>
                            <div class="col-4">
                                <small class="text-muted ms-1">Group / Section</small>
                            </div>
                        </div>

                        <button type="button" id="addRowBtn" class="btn btn-outline-secondary btn-sm mb-4">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Subject
                        </button>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Save My Subjects
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 1;

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const container = document.getElementById('subjectRows');

        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 subject-row';
        row.innerHTML = `
            <div class="col-7">
                <input type="text"
                       name="subjects[${rowIndex}][code]"
                       class="form-control"
                       placeholder="Course Code (e.g. IT3201)"
                       list="subjectList"
                       autocomplete="off"
                       required>
            </div>
            <div class="col-4">
                <input type="text"
                       name="subjects[${rowIndex}][group]"
                       class="form-control"
                       placeholder="Group (e.g. 1)">
            </div>
            <div class="col-1 d-flex align-items-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger remove-row"
                        title="Remove">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        rowIndex++;
    });

    // Remove row on click (delegated)
    document.getElementById('subjectRows').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) {
            btn.closest('.subject-row').remove();
        }
    });
});
</script>
@endpush

@endsection