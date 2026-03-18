@extends('layouts.default')

@section('content')

{{-- Page Header --}}
<div class="dash-header">
    <div class="dash-header__left">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Department</li>
            </ol>
        </nav>
        <h1 class="dash-header__title">Faculty Directory</h1>
        <p class="dash-header__subtitle">Browse and manage all faculty members in the department.</p>
    </div>
    <div class="dash-header__actions">
        {{-- Placeholder for future actions e.g. Invite Faculty --}}
    </div>
</div>

{{-- Filter Bar --}}
<div class="dash-filters mb-4">
    <div class="dash-filters__selects">

        <div class="dash-filter-group">
            <label class="dash-filter-group__label" for="facultySearch">
                <i class="bi bi-search me-1"></i> Search
            </label>
            <div class="search-wrap">
                <input type="text"
                       id="facultySearch"
                       class="form-control form-control-sm"
                       placeholder="Name or email...">
                <button class="search-clear d-none" id="searchClear" aria-label="Clear search">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="dash-filter-group">
            <label class="dash-filter-group__label" for="subjectFilter">
                <i class="bi bi-book me-1"></i> Course
            </label>
            <select id="subjectFilter" class="form-select form-select-sm">
                <option value="">All Courses</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">
                        {{ $subject->course_code }} — {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="dash-filters__links">
        <span class="results-count" id="resultsCount">
            {{ $faculty->count() }} {{ Str::plural('member', $faculty->count()) }}
        </span>
    </div>
</div>

{{-- Faculty Grid --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-3" id="facultyContainer">

    @forelse($faculty as $member)
        @php
            $isAdmin    = $member->roles->where('name', 'admin')->count() > 0;
            $initial    = strtoupper(substr($member->name, 0, 1));
            $subjectIds = $member->teachingSubjects->pluck('id')->implode(',');
        @endphp

        <div class="col faculty-card"
             data-subjects="{{ $subjectIds }}"
             data-name="{{ strtolower($member->name) }}"
             data-email="{{ strtolower($member->email) }}">

            <div class="fcard">

                {{-- Avatar + info + role badge --}}
                <div class="fcard__avatar-wrap">
                    <div class="fcard__avatar {{ $isAdmin ? 'fcard__avatar--admin' : 'fcard__avatar--faculty' }}">
                        {{ $initial }}
                    </div>
                    <div class="fcard__info">
                        <h6 class="fcard__name">{{ $member->name }}</h6>
                        <p class="fcard__email">{{ $member->email }}</p>
                    </div>
                    @if($isAdmin)
                        <span class="role-badge role-badge--admin">
                            <i class="bi bi-shield-fill me-1"></i>Admin
                        </span>
                    @else
                        <span class="role-badge role-badge--teacher">
                            <i class="bi bi-person-fill me-1"></i>Faculty
                        </span>
                    @endif
                </div>

                {{-- Courses --}}
                <div class="fcard__courses">
                    @if($member->teachingSubjects->count() > 0)
                        <p class="fcard__courses-label">Courses</p>
                        <div class="fcard__badges">
                            @foreach($member->teachingSubjects as $subject)
                                <span class="fcard__badge">{{ $subject->course_code }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="fcard__no-courses">
                            <i class="bi bi-dash-circle me-1"></i>No assigned courses
                        </p>
                    @endif
                </div>

                {{-- Footer action --}}
                <div class="fcard__footer">
                    <a href="{{ route('admin.evaluatee.evaluateeDetails', ['id' => $member->id]) }}"
                       class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-person-lines-fill me-1"></i> View Profile
                    </a>
                </div>

            </div>
        </div>

    @empty
        <div class="col-12">
            <div class="dash-empty py-5">
                <i class="bi bi-people dash-empty__icon"></i>
                <span>No faculty members found.</span>
            </div>
        </div>
    @endforelse

</div>

{{-- No search results state — hidden by default, shown by JS --}}
<div class="dash-empty py-5 d-none" id="noResults">
    <i class="bi bi-search dash-empty__icon"></i>
    <span>
        No faculty match your search.<br>
        <small>Try a different name, email, or course.</small>
    </span>
</div>

@endsection

@push('scripts')
    @vite('resources/js/admin/department.js')
@endpush