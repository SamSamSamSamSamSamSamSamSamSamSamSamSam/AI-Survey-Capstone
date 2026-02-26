@extends('layouts.default')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-semibold mb-0">Word Cloud / Frequent Phrases</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    @if(empty($words))
        <div class="text-center text-muted py-5">
            <p class="mb-0">No text responses found.</p>
        </div>
    @else
        @php
            $max = max($words);
            $min = min($words);
            $range = max(1, $max - $min);
        @endphp

        <div class="p-4 border rounded shadow-sm bg-white text-center" style="min-height: 250px;">
            @foreach($words as $w => $c)
                @php
                    $size = 12 + intval((($c - $min) / $range) * 28);
                    $link = $wordLinks[$w] ?? route('admin.analysis.questionAnalysis', ['survey_id' => $surveyId ?? null, 'q' => $w]);
                @endphp
                <a href="{{ $link }}"
                   class="me-2"
                   style="font-size: {{ $size }}px; color: #2c3e50; text-decoration: none; transition: color 0.2s;"
                   onmouseover="this.style.color='#0d6efd';"
                   onmouseout="this.style.color='#2c3e50';">
                    {{ $w }}
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection