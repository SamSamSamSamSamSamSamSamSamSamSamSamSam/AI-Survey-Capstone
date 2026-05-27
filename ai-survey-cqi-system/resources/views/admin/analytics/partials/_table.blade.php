<div id="analytics-table-container">
    <div class="card">
        @if ($analytics->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
                <p class="empty-state-text">No active analytics found matching your criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table data-table align-middle mb-0" id="faculty-analytics-table">
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Course</th>
                            <th>Semester</th>
                            <th class="text-center">Responses</th>
                            <th class="text-center">Avg Rating</th>
                            <th>Sentiment</th>
                            <th>Last Computed</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($analytics as $analytic)
                        {{-- Logic: Only active records reach this loop now --}}
                        <tr id="row-{{ $analytic->id }}">
                            <td>
                                <div class="user-cell">
                                    {{-- <div class="user-avatar-sm">
                                        {{ strtoupper(substr($analytic->faculty->name, 0, 2)) }}
                                    </div> --}}
                                    <span class="fw-500">{{ $analytic->faculty->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-mono" style="font-size:.8rem;">
                                    {{ $analytic->survey->offering->subject->course_code }} - {{ $analytic->survey->offering->group_number }}
                                </div>
                                <div class="text-muted-sm">
                                    {{ Str::limit($analytic->survey->offering->subject->name, 28) }}
                                </div>
                            </td>
                            <td class="text-muted-sm">
                                {{ $analytic->survey->offering->semester->full_label }}
                            </td>
                            <td class="text-center">
                                <span class="count-badge">{{ $analytic->response_count }}</span>
                            </td>
                            <td class="text-center">
                                @if ($analytic->avg_rating)
                                    @php $r = $analytic->avg_rating; @endphp
                                    <span class="rating-score {{ $r >= 4 ? 'rating-score--high' : ($r >= 3 ? 'rating-score--mid' : 'rating-score--low') }}">
                                        {{ number_format($r, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted-sm">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($analytic->positive_sentiment_percent !== null)
                                    <div class="sentiment-mini-bar">
                                        <div class="sentiment-mini-bar__track">
                                            <div class="sentiment-mini-bar__fill sentiment-mini-bar__fill--pos" style="width: {{ $analytic->positive_sentiment_percent }}%"></div>
                                            <div class="sentiment-mini-bar__fill sentiment-mini-bar__fill--neu" style="width: {{ $analytic->neutral_sentiment_percent ?? 0 }}%"></div>
                                            <div class="sentiment-mini-bar__fill sentiment-mini-bar__fill--neg" style="width: {{ $analytic->negative_sentiment_percent }}%"></div>
                                        </div>
                                        <div class="sentiment-mini-bar__labels">
                                            <span class="sentiment-mini-bar__label--pos">{{ number_format($analytic->positive_sentiment_percent, 0) }}%</span>
                                            <span class="sentiment-mini-bar__label--neg">{{ number_format($analytic->negative_sentiment_percent, 0) }}%</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted-sm">No text responses</span>
                                @endif
                            </td>
                            <td class="text-muted-sm">
                                {{ $analytic->last_computed_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="{{ route('admin.analytics.show', $analytic->id) }}" class="btn btn-sm btn-icon" title="View Analytics">
                                        <i class="bi bi-graph-up"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.analytics.recompute', $analytic->survey_id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-icon btn-warning" title="Recompute Analytics">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>

                                    {{-- Archive Form with AJAX class --}}
                                    {{-- <form method="POST" 
                                          action="{{ route('admin.analytics.destroy', $analytic->id) }}" 
                                          class="d-inline archive-form"
                                          data-id="{{ $analytic->id }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form> --}}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($analytics->hasPages())
                <div class="table-pagination p-3">
                    {{ $analytics->links() }}
                </div>
            @endif
        @endif
    </div>
</div>