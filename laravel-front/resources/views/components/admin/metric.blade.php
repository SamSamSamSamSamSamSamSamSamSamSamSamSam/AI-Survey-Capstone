@props([
    'title' => 'Metric',
    'value' => 0,
    'icon' => null,
    'color' => 'primary'
])

<div class="col-md-3 mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h6 class="text-muted mb-1">{{ $title }}</h6>
                <h3 class="fw-bold mb-0">{{ $value }}</h3>
            </div>
            @if($icon)
                <div class="rounded-circle bg-{{ $color }} text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="{{ $icon }}"></i>
                </div>
            @endif
        </div>
    </div>
</div>
