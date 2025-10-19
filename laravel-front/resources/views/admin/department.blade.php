@extends('layouts.default')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Faculty Directory</h5>
                    <div class="d-flex">
                        <input type="text" id="facultySearch" class="form-control form-control-sm me-2" placeholder="Search faculty...">
                        <select id="subjectFilter" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->course_code }} - {{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="facultyContainer">
                        @forelse($faculty as $member)
                            <div class="col faculty-card" data-subjects="{{ $member->teachingSubjects->pluck('id')->implode(',') }}">
                                <div class="card h-100 shadow-sm faculty-member">
                                    <div class="card-body text-center p-3">
                                        <div class="position-relative">
                                            <div class="avatar-placeholder bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                <span class="text-primary fw-bold fs-4">{{ substr($member->name, 0, 1) }}</span>
                                            </div>
                                            @if($member->roles->where('name', 'admin')->count() > 0)
                                                <span class="position-absolute top-0 start-70 translate-middle badge rounded-pill bg-warning">
                                                    Admin
                                                </span>
                                            @endif
                                        </div>
                                        <h5 class="card-title mb-1">{{ $member->name }}</h5>
                                        <p class="text-muted small mb-2">{{ $member->email }}</p>
                                        
                                        @if($member->teachingSubjects->count() > 0)
                                            <div class="border-top pt-2 mt-2">
                                                <h6 class="text-muted small mb-1">Courses:</h6>
                                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                                    @foreach($member->teachingSubjects as $subject)
                                                        <span class="badge bg-light text-dark border">{{ $subject->course_code }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted small">No assigned subjects</p>
                                        @endif
                                    </div>
                                    <div class="card-footer bg-transparent d-flex justify-content-between">
                                        <button class="btn btn-sm btn-outline-primary">View Profile</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    No faculty members found.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-placeholder {
    transition: transform 0.3s ease;
}
.faculty-member:hover .avatar-placeholder {
    transform: scale(1.1);
}
.faculty-member {
    transition: all 0.3s ease;
}
.faculty-member:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.badge.bg-light {
    font-size: 0.7rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('facultySearch');
    const subjectFilter = document.getElementById('subjectFilter');
    const facultyCards = document.querySelectorAll('.faculty-card');
    
    function filterFaculty() {
        const searchTerm = searchInput.value.toLowerCase();
        const subjectId = subjectFilter.value;
        
        facultyCards.forEach(card => {
            const name = card.querySelector('.card-title').textContent.toLowerCase();
            const email = card.querySelector('.text-muted').textContent.toLowerCase();
            const subjects = card.getAttribute('data-subjects').split(',');
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesSubject = subjectId === '' || subjects.includes(subjectId);
            
            if (matchesSearch && matchesSubject) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterFaculty);
    subjectFilter.addEventListener('change', filterFaculty);
});
</script>
@endsection