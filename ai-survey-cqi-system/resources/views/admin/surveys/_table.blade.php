@if ($surveys->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-ui-checks-grid"></i></div>
        <p class="empty-state-text">No surveys found.</p>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Create First Survey
        </a>
    </div>
@else
    <div class="table-responsive">
        <table class="table data-table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Survey & Offering</th>
                    <th>Target</th>
                    <th class="text-center">Timeline</th>
                    <th class="text-center">Responses</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($surveys as $survey)
                <tr class="{{ $survey->trashed() ? 'row-muted' : '' }}">
                    {{-- GROUPED TITLE & OFFERING --}}
<td class="ps-3">
    <div class="fw-500 text-dark">{{ $survey->title }}</div>
    
    @if($survey->offering)
        <div class="text-muted-sm d-flex align-items-center mt-1">
            <a href="{{ route('admin.offerings.show', $survey->offering->id) }}" 
               class="text-decoration-none hover-underline text-primary fw-600 me-2">
                {{ $survey->offering->subject->course_code }}
            </a>

            <a href="{{ route('admin.offerings.show', $survey->offering->id) }}" 
               class="text-decoration-none">
                <span class="badge bg-light text-dark border fw-normal me-2" style="font-size: 0.65rem; cursor: pointer;">
                    G{{ $survey->offering->group_number }}
                </span>
            </a>

            <span class="opacity-75">| {{ $survey->offering->teacher->name }}</span>
        </div>
    @else
        <div class="text-muted-sm italic">General Survey</div>
    @endif
</td>

                    {{-- TARGET ROLE --}}
                    <td>
                        <span class="role-pill role-pill--{{ $survey->targetRole->name }}">
                            {{ ucfirst($survey->targetRole->name) }}
                        </span>
                    </td>

                    {{-- ACTIVE PERIOD / TIMELINE --}}
                    <td class="text-center">
                        @if($survey->end_date)
                            <div class="small {{ $survey->end_date->isPast() ? 'text-muted' : 'text-dark' }}">
                                {{ $survey->end_date->format('M d, Y') }}
                            </div>
                            @if(!$survey->end_date->isPast() && $survey->is_active)
                                <div class="text-primary" style="font-size: .7rem;">
                                    Ends {{ $survey->end_date->diffForHumans() }}
                                </div>
                            @endif
                        @else
                            <span class="text-muted-sm">No deadline</span>
                        @endif
                    </td>

                    {{-- RESPONSES COUNT --}}
                    <td class="text-center">
                        <span class="count-badge count-badge--responses">
                            {{ $survey->attempts()->whereNotNull('submitted_at')->count() }}
                        </span>
                    </td>

                    {{-- STATUS --}}
                    <td>
                        @if ($survey->trashed())
                            <span class="status-pill status-pill--archived">
                                <i class="bi bi-archive me-1"></i> Archived
                            </span>
                        @elseif ($survey->is_active)
                            <span class="status-pill status-pill--active">
                                <i class="bi bi-broadcast me-1"></i> Active
                            </span>
                        @else
                            @php
                                $responses = $survey->responses_count ?? 0;
                                // Access the eager-loaded count safely via the offering relation
                                $enrollments = $survey->offering ? ($survey->offering->enrollments_count ?? 0) : 0;
                            @endphp

                            @if ($enrollments > 0 && $responses >= $enrollments)
                                <span class="status-pill status-pill--previous text-muted bg-light border">
                                    <i class="bi bi-check-all me-1"></i> Completed
                                </span>
                            @elseif ($responses > 0 && $responses < $enrollments)
                                <span class="status-pill status-pill--previous text-muted bg-light border">
                                    <i class="bi bi-dash-circle-fill me-1"></i> Closed (Partial)
                                </span>
                            @else
                                <span class="status-pill status-pill--inactive">
                                    <i class="bi bi-slash-circle me-1"></i> Inactive
                                </span>
                            @endif
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td class="text-end pe-3">
                        <div class="table-actions">
                                                        
                            @if (! $survey->trashed())
                                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-sm btn-icon" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                {{-- Edit: Only show if NOT active --}}
                                @if (!$survey->is_active)
                                    <a href="{{ route('admin.surveys.edit', $survey->id) }}" 
                                        class="btn btn-sm btn-icon" 
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                {{-- Toggle Active/Inactive (Always visible) --}}
                                <form method="POST" action="{{ route('admin.surveys.toggle-active', $survey->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-icon {{ $survey->is_active ? 'btn-icon--warning' : 'btn-icon--success' }}"
                                            title="{{ $survey->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $survey->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                                    </button>
                                </form>

                                {{-- Archive: Only show if NOT active --}}
                                @if (!$survey->is_active)
                                    <form method="POST" action="{{ route('admin.surveys.destroy', $survey->id) }}" class="d-inline" data-confirm="Archive this survey?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Archive">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                {{-- Restore for Trashed items --}}
                                <form method="POST" action="{{ route('admin.surveys.restore', $survey->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-icon btn-icon--success" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if ($surveys->hasPages())
        <div class="table-pagination mt-3">
            {{ $surveys->links() }}
        </div>
    @endif
@endif