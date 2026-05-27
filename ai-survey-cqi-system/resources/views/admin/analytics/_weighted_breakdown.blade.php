{{--
    ============================================================
    resources/views/admin/analytics/_weighted_breakdown.blade.php

    Weighted Category Achievement Breakdown
    Include this partial in admin/analytics/show.blade.php
    inside the quantitative section.

    Variables expected:
      $analytic   — FacultyAnalytics model instance
    ============================================================
--}}

@php
    /** @var \App\Services\CategoryWeightService $weightSvc */
    $weightSvc = app(\App\Services\CategoryWeightService::class);

    $scores           = $analytic->category_scores ?? [];
    $weights          = $scores['_weights']          ?? [];
    $weightedScores   = $scores['_weighted_scores']  ?? [];
    $achievements     = $scores['_achievements']     ?? [];
    $overallWeighted  = $scores['_overall_weighted_score'] ?? null;
    $stats            = $scores['_overall_stats']    ?? [];

    // Raw means — all keys not starting with underscore
    $rawMeans = array_filter(
        $scores,
        fn($k) => !str_starts_with((string)$k, '_'),
        ARRAY_FILTER_USE_KEY
    );

    $hasWeights = !empty($weights) && $overallWeighted !== null;
    $scaleMax   = $analytic->survey?->questions
                    ->where('question_type', 'rating')
                    ->first()
                    ?->scale?->max_value ?? 5;
@endphp

{{-- ── Weighted Achievement Breakdown ─────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-600" style="font-size:.875rem;">
            <i class="bi bi-bar-chart-steps me-2 text-primary"></i>
            Category Performance Breakdown
        </span>
        @if ($hasWeights)
        <span class="badge"
              style="background:#1e3a5f;color:#fff;font-size:.75rem;padding:4px 10px;border-radius:20px;">
            Overall Achievement: {{ number_format($overallWeighted, 2) }}%
        </span>
        @endif
    </div>

    <div class="card-body p-0">

        @if (empty($rawMeans))
            <div class="text-center py-4 text-muted" style="font-size:.875rem;">
                <i class="bi bi-bar-chart-line me-2"></i>
                No category score data available yet. Recompute analytics to generate scores.
            </div>
        @else

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0"
                   style="font-size:.875rem;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="font-size:.75rem;text-transform:uppercase;color:#6b7280;
                                   padding:.6rem 1rem;">
                            Category
                        </th>
                        <th class="text-center"
                            style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">
                            Mean Score
                        </th>
                        @if ($hasWeights)
                        <th class="text-center"
                            style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">
                            Weight
                        </th>
                        <th class="text-center"
                            style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">
                            Achievement
                        </th>
                        <th class="text-center"
                            style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">
                            Contribution
                        </th>
                        @endif
                        <th style="font-size:.75rem;text-transform:uppercase;color:#6b7280;">
                            Performance
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rawMeans as $category => $mean)
                    @php
                        $mean        = (float) $mean;
                        $weight      = (float) ($weights[$category]        ?? 0);
                        $weighted    = (float) ($weightedScores[$category] ?? 0);
                        $achievement = (float) ($achievements[$category]   ?? ($mean / $scaleMax * 100));
                        $interp      = $weightSvc->interpret($achievement);
                        // blade-friendly aliases matching the old array shape: [0]=label,[1]=color,[2]=bg
                        $interp      = [$interp['label'], $interp['color'], $interp['bg']];
                    @endphp
                    <tr style="border-bottom:0.5px solid #f3f4f6;">
                        <td style="padding:.65rem 1rem;font-weight:500;color:#111;">
                            {{ $category }}
                        </td>
                        <td class="text-center" style="font-weight:600;color:#1e3a5f;">
                            {{ number_format($mean, 2) }} / {{ $scaleMax }}
                        </td>
                        @if ($hasWeights)
                        <td class="text-center">
                            <span style="font-size:.8rem;color:#6b7280;font-weight:500;">
                                {{ number_format($weight, 2) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge"
                                  style="background:{{ $interp[2] }};color:{{ $interp[1] }};
                                         font-size:.75rem;padding:3px 8px;border-radius:10px;">
                                {{ number_format($achievement, 1) }}%
                            </span>
                        </td>
                        <td class="text-center" style="font-weight:600;color:#374151;">
                            {{ number_format($weighted, 2) }}
                            <span style="font-size:.7rem;color:#9ca3af;font-weight:400;">pts</span>
                        </td>
                        @endif
                        <td style="min-width:180px;padding:.65rem 1rem;">
                            <div style="height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;margin-bottom:3px;">
                                <div style="height:100%;border-radius:3px;
                                            background:{{ $interp[1] }};
                                            width:{{ min($achievement, 100) }}%;
                                            transition:width .3s;">
                                </div>
                            </div>
                            <span style="font-size:.7rem;color:{{ $interp[1] }};font-weight:500;">
                                {{ $interp[0] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                @if ($hasWeights)
                <tfoot>
                    <tr style="background:#f0f4f8;border-top:2px solid #e5e7eb;">
                        <td style="padding:.65rem 1rem;font-weight:700;color:#111;">
                            Overall Weighted Score
                        </td>
                        <td class="text-center" style="font-weight:600;color:#6b7280;">
                            {{ number_format($analytic->avg_rating, 2) }} / {{ $scaleMax }}
                            <div style="font-size:.7rem;color:#9ca3af;">simple avg</div>
                        </td>
                        <td class="text-center" style="font-weight:700;color:#374151;">
                            100%
                        </td>
                        <td class="text-center">
                            @php
                                $ov = (float) $overallWeighted;
                                $ovInterpRaw = $weightSvc->interpret($ov);
                                $ovInterp = [$ovInterpRaw['label'], $ovInterpRaw['color'], $ovInterpRaw['bg']];
                            @endphp
                            <span class="badge"
                                  style="background:{{ $ovInterp[2] }};color:{{ $ovInterp[1] }};
                                         font-size:.8rem;padding:4px 10px;border-radius:10px;font-weight:700;">
                                {{ number_format($overallWeighted, 2) }}%
                            </span>
                        </td>
                        <td class="text-center" style="font-weight:700;color:#1e3a5f;font-size:1rem;">
                            {{ number_format($overallWeighted, 2) }}
                            <span style="font-size:.7rem;color:#9ca3af;font-weight:400;">/ 100</span>
                        </td>
                        <td style="padding:.65rem 1rem;">
                            <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;margin-bottom:3px;">
                                <div style="height:100%;border-radius:4px;
                                            background:{{ $ovInterp[1] }};
                                            width:{{ min($overallWeighted, 100) }}%;
                                            transition:width .3s;">
                                </div>
                            </div>
                            <span style="font-size:.75rem;color:{{ $ovInterp[1] }};font-weight:600;">
                                {{ $ovInterp[0] }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
                @endif

            </table>
        </div>

        @if (!$hasWeights)
        <div class="px-4 pb-3 pt-2">
            <div class="alert alert-info py-2 px-3 mb-0" style="font-size:.8rem;">
                <i class="bi bi-info-circle me-1"></i>
                No category weights configured for this survey.
                Showing raw mean scores only.
                Configure weights on the
                <a href="{{ route('admin.surveys.show', $analytic->survey_id) }}"
                   class="alert-link">survey page</a>
                and recompute analytics to see weighted achievement scores.
            </div>
        </div>
        @endif

        {{-- Descriptive stats row --}}
        @if (!empty($stats))
        <div style="display:flex;gap:1.5rem;padding:.75rem 1rem;
                    border-top:0.5px solid #f3f4f6;background:#fafafa;">
            <div style="font-size:.8rem;color:#6b7280;">
                <span style="font-weight:600;color:#374151;">Median:</span>
                {{ number_format($stats['median'] ?? 0, 2) }}
            </div>
            <div style="font-size:.8rem;color:#6b7280;">
                <span style="font-weight:600;color:#374151;">Mode:</span>
                {{ $stats['mode'] ?? '—' }}
            </div>
            <div style="font-size:.8rem;color:#6b7280;">
                <span style="font-weight:600;color:#374151;">Std Dev:</span>
                {{ number_format($stats['std_dev'] ?? 0, 2) }}
            </div>
        </div>
        @endif

        @endif {{-- end not empty --}}
    </div>
</div>