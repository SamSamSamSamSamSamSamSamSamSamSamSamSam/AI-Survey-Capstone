<div class="card result-card h-100">
    <div class="card-body">
        <h6 class="card-title text-muted">{{ $courseName ?? 'Course Name' }}</h6>
        <hr>
        <p class="card-text mb-0">{{ $schedule }}</p>
        <p class="card-text mb-0">{{ $group }}</p>
    </div>
    @if($available)
        <div class="view-btn">VIEW RESULT</div>
    @else
        <div class="view-btn bg-secondary">RESULT UNAVAILABLE</div>
    @endif
</div>
