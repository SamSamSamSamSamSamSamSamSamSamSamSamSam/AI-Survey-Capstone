@extends('layouts.default')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Generate CQI Report</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.cqi') }}" method="GET" target="_blank" id="reportForm">
                        {{-- @csrf is not required for GET requests, but included here for completeness if method changes --}}
                        {{-- @csrf --}} 
                        <div class="mb-3">
                            <label class="form-label fw-bold">Report Range</label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="range_type" id="autoRange" value="auto" checked>
                                    <label class="form-check-label" for="autoRange">
                                        Last 5 Months (Automatic)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="range_type" id="customRange" value="custom">
                                    <label class="form-check-label" for="customRange">
                                        Custom Range
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="customRangeFields" class="row g-3 mt-2" style="display:none;">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Month & Year</label>
                                <input type="month" name="start_date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Month & Year</label>
                                <input type="month" name="end_date" id="end_date" class="form-control">
                                <div class="invalid-feedback" id="dateError">
                                    The End Date must be after or the same as the Start Date.
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success" id="submitButton">
                                <i class="bi bi-file-earmark-pdf"></i> Generate PDF Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- --- JAVASCRIPT VALIDATION --- --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.getElementById('reportForm');
    const custom = document.getElementById('customRange');
    const customFields = document.getElementById('customRangeFields');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const dateErrorDiv = document.getElementById('dateError');

    // Initial state setup and event listeners for radio buttons
    document.getElementById('autoRange').addEventListener('change', toggleFields);
    custom.addEventListener('change', toggleFields);

    function toggleFields() {
        // Toggle display
        customFields.style.display = custom.checked ? 'flex' : 'none';
        
        // Remove validation state when switching away from custom
        if (!custom.checked) {
            endDateInput.classList.remove('is-invalid');
            dateErrorDiv.style.display = 'none';
        }
    }

    // --- Validation Logic ---
    reportForm.addEventListener('submit', function(event) {
        // Only validate if the 'Custom Range' radio button is checked
        if (custom.checked) {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            // Check if both dates are provided
            if (!startDate || !endDate) {
                // If inputs are required, you would handle setting 'is-invalid' here for missing data.
                // Assuming HTML 'required' or backend handles missing data, we focus on chronological order.
                return true; // Let form submit if dates are empty and not required for now
            }

            // Compare dates
            // HTML <input type="month"> value format is YYYY-MM, which is easily comparable as strings
            if (startDate > endDate) {
                // Prevent form submission
                event.preventDefault(); 
                
                // Show error message
                endDateInput.classList.add('is-invalid');
                dateErrorDiv.style.display = 'block';
            } else {
                // Dates are valid, remove error state and allow submission
                endDateInput.classList.remove('is-invalid');
                dateErrorDiv.style.display = 'none';
            }
        }
    });

    // Optional: Clear error state instantly when the user corrects the End Date
    endDateInput.addEventListener('change', function() {
        if (endDateInput.classList.contains('is-invalid') && endDateInput.value >= startDateInput.value) {
            endDateInput.classList.remove('is-invalid');
            dateErrorDiv.style.display = 'none';
        }
    });

    // Run once on load to set initial state
    toggleFields();
});
</script>
@endsection