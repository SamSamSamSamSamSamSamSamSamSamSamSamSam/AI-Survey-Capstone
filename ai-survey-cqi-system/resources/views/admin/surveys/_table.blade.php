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
                    <th>Survey Details</th>
                    <th>Target Offerings</th>
                    <th>Audience</th>
                    <th class="text-center">Questions</th>
                    <th class="text-center">Responses</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($surveys as $survey)
                <tr class="{{ $survey->trashed() ? 'row-muted' : '' }}">
                    {{-- SURVEY TITLE & DESCRIPTION --}}
                    <td>
                        <div class="fw-500 text-dark">{{ $survey->title }}</div>
                        @if($survey->description)
                            <div class="text-muted-sm text-truncate" style="max-width: 200px;" title="{{ $survey->description }}">
                                {{ $survey->description }}
                            </div>
                        @endif
                    </td>

                    {{-- LOOP THROUGH OFFERING (Single Relationship) --}}
                    <td>
                        @if(!$survey->offering)
                            <span class="text-muted-sm italic">No offering assigned</span>
                        @else
                            <div class="mb-1 p-1 border-start border-primary border-3 bg-light-subtle rounded-end" style="font-size:.75rem;">
                                <div class="fw-semibold">
                                    {{ $survey->offering->subject->course_code }} — Group {{ $survey->offering->group_number }}
                                </div>
                                <div class="text-muted d-flex justify-content-between">
                                    <span>{{ $survey->offering->teacher->name }}</span>
                                    <span class="ms-2 opacity-75">
                                        {{ $survey->offering->semester->semester_number }} — {{ $survey->offering->semester->academic_start_year }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </td>

                    {{-- TARGET ROLE --}}
                    <td>
                        <span class="role-pill role-pill--{{ $survey->targetRole->name }}">
                            {{ ucfirst($survey->targetRole->name) }}
                        </span>
                    </td>

                    {{-- QUESTIONS COUNT --}}
                    <td class="text-center">
                        <span class="count-badge">
                            {{ $survey->questions_count }}
                        </span>
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
                            <span class="status-pill status-pill--archived"><i class="bi bi-archive me-1"></i>Archived</span>
                        @elseif ($survey->is_active)
                            <span class="status-pill status-pill--active"><i class="bi bi-check-circle me-1"></i>Active</span>
                        @else
                            <span class="status-pill status-pill--inactive"><i class="bi bi-pause-circle me-1"></i>Inactive</span>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td class="text-end">
                        <div class="table-actions">
                            <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-sm btn-icon" title="View"><i class="bi bi-eye"></i></a>
                            
                            @if (! $survey->trashed())
                                <a href="{{ $survey->is_active ? '#' : route('admin.surveys.edit', $survey->id) }}" 
                                    class="btn btn-sm btn-icon {{ $survey->is_active ? 'disabled' : '' }}" 
                                    title="{{ $survey->is_active ? 'Cannot edit while active' : 'Edit' }}"
                                    style="{{ $survey->is_active ? 'pointer-events: auto; cursor: not-allowed;' : '' }}">
                                    <i class="bi {{ $survey->is_active ? 'bi-lock-fill text-muted' : 'bi-pencil' }}"></i>
                                </a>

                                <form method="POST" action="{{ route('admin.surveys.toggle-active', $survey->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-icon {{ $survey->is_active ? 'btn-icon--warning' : 'btn-icon--success' }}"
                                            title="{{ $survey->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $survey->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.surveys.destroy', $survey->id) }}" class="d-inline" data-confirm="Archive this survey?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-icon--danger" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.surveys.restore', $survey->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-icon btn-icon--success" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
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