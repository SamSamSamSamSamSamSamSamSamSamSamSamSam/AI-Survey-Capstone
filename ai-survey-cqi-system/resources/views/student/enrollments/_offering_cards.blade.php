{{-- student/enrollments/_offering_cards.blade.php --}}

@if($availableOfferings->count() > 0)
    <div class="enrollment-offering-grid mb-4">
        @foreach ($availableOfferings as $offering)
            <div class="enrollment-offering-card">
                <div class="enrollment-offering-card__code">
                    {{ $offering->subject->course_code }}
                </div>
                <div class="enrollment-offering-card__name">
                    {{ $offering->subject->name }}
                </div>
                
                @if ($offering->offeringType)
                    <span class="role-pill role-pill--faculty mb-2">{{ $offering->offeringType->name }}</span>
                @endif

                <div class="enrollment-offering-card__meta">
                    <div><i class="bi bi-person me-1"></i>{{ $offering->teacher->name }}</div>
                    <div><i class="bi bi-journal me-1"></i>{{ $offering->subject->units }} unit(s)</div>
                    @if ($offering->group_number)
                        <div><i class="bi bi-people me-1"></i>Group {{ $offering->group_number }}</div>
                    @endif
                </div>

                <form method="POST" action="{{ route('student.enrollments.store') }}">
                    @csrf
                    <input type="hidden" name="offering_id" value="{{ $offering->id }}">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                        <i class="bi bi-plus-lg me-1"></i> Enroll
                    </button>
                </form>
            </div>  
        @endforeach
    </div>

    <div class="d-flex justify-content-center">
        {{ $availableOfferings->links() }}
    </div>

@else
    {{-- This shows inside the offerings-container when a search fails --}}
    <div class="text-center py-5">
        <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
        <p class="mt-2 text-muted">No courses found matching "{{ request('search') }}"</p>
        <button type="button" class="btn btn-link btn-sm" onclick="document.getElementById('searchInput').value=''; document.getElementById('searchInput').dispatchEvent(new Event('keyup'));">
            Clear search
        </button>
    </div>
@endif