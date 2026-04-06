@extends('layouts.default')

@section('content')

<div class="page-header mb-4">
    <div class="page-header-content">
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Configure system preferences, thresholds, and AI integration.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ── Institution Info ──────────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary"></i>
                    <h5 class="mb-0 fw-semibold">Institution Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Institution Name</label>
                            <input type="text" name="institution_name" class="form-control"
                                   value="{{ old('institution_name', $settings['institution_name']) }}"
                                   placeholder="e.g. University of Cebu">
                            <div class="form-text">Appears on generated PDF reports.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department Name</label>
                            <input type="text" name="department_name" class="form-control"
                                   value="{{ old('department_name', $settings['department_name']) }}"
                                   placeholder="e.g. Department of Computer Science">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Report Title Prefix</label>
                            <input type="text" name="report_title_prefix" class="form-control"
                                   value="{{ old('report_title_prefix', $settings['report_title_prefix']) }}"
                                   placeholder="e.g. CQI Summary Report">
                            <div class="form-text">Prefix used when naming generated PDF files.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Analytics Thresholds ──────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary"></i>
                    <h5 class="mb-0 fw-semibold">Analytics & CQI Thresholds</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Target Rating</label>
                            <div class="input-group">
                                <input type="number" name="target_rating" class="form-control"
                                       value="{{ old('target_rating', $settings['target_rating']) }}"
                                       step="0.1" min="1" max="5">
                                <span class="input-group-text">/ 5.0</span>
                            </div>
                            <div class="form-text">Minimum acceptable faculty rating. Used in dashboard KPIs and gap analysis.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CQI Gap — High Priority</label>
                            <div class="input-group">
                                <input type="number" name="cqi_priority_high" class="form-control"
                                       value="{{ old('cqi_priority_high', $settings['cqi_priority_high']) }}"
                                       step="0.01" min="0" max="5">
                                <span class="input-group-text">gap ≥</span>
                            </div>
                            <div class="form-text">Gap value at which a category is flagged as highest priority.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CQI Gap — Medium Priority</label>
                            <div class="input-group">
                                <input type="number" name="cqi_priority_medium" class="form-control"
                                       value="{{ old('cqi_priority_medium', $settings['cqi_priority_medium']) }}"
                                       step="0.01" min="0" max="5">
                                <span class="input-group-text">gap ≥</span>
                            </div>
                            <div class="form-text">Gap value at which a category is flagged as medium priority.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Min Responses for Top Performers</label>
                            <input type="number" name="min_responses_threshold" class="form-control"
                                   value="{{ old('min_responses_threshold', $settings['min_responses_threshold']) }}"
                                   min="1" max="100">
                            <div class="form-text">Minimum number of rating responses needed before a faculty member appears in the Top Performers table.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── AI Configuration ──────────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-robot text-primary"></i>
                    <h5 class="mb-0 fw-semibold">AI Configuration</h5>
                </div>
                <div class="card-body">

                    {{-- API key status banner --}}
                    @if($settings['ai_api_key_set'])
                        <div class="alert alert-success py-2 d-flex align-items-center justify-content-between mb-4">
                            <span>
                                <i class="bi bi-key-fill me-2"></i>
                                An API key is currently stored and active.
                            </span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="testKeyBtn">
                                    <i class="bi bi-wifi me-1"></i> Test Connection
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="if(confirm('Remove the stored API key?')) document.getElementById('clear-key-form').submit();">
                                    <i class="bi bi-trash me-1"></i> Remove Key
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning py-2 mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No API key configured. AI-assisted CQI recommendations will not be available until a key is saved.
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">AI Provider</label>
                            <select name="ai_provider" class="form-select" id="aiProviderSelect">
                                <option value="gemini"  {{ $settings['ai_provider'] === 'gemini'  ? 'selected' : '' }}>Google Gemini</option>
                                <option value="claude"  {{ $settings['ai_provider'] === 'claude'  ? 'selected' : '' }}>Anthropic Claude</option>
                                <option value="openai"  {{ $settings['ai_provider'] === 'openai'  ? 'selected' : '' }}>OpenAI (ChatGPT)</option>
                            </select>
                            <div class="form-text mt-1" id="providerHint"></div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                API Key
                                @if($settings['ai_api_key_set'])
                                    <span class="badge bg-success ms-1">Key stored</span>
                                @endif
                            </label>
                            <div class="input-group">
                                <input type="password" name="ai_api_key" class="form-control"
                                       id="apiKeyInput"
                                       placeholder="{{ $settings['ai_api_key_set'] ? 'Enter a new key to replace the current one' : 'Paste your API key here' }}"
                                       autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" id="toggleKey">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Leave blank to keep the existing key unchanged. The key is stored encrypted.</div>
                        </div>
                    </div>

                    {{-- Test result area --}}
                    <div id="testResult" class="mt-3" style="display:none;"></div>

                </div>
            </div>
        </div>

        {{-- ── Save Button ───────────────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Save Settings
                </button>
            </div>
        </div>

    </div>
</form>

<form id="clear-key-form" action="{{ route('admin.settings.clearKey') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle API key visibility ──────────────────────────────────────────────
    document.getElementById('toggleKey').addEventListener('click', function () {
        const input = document.getElementById('apiKeyInput');
        const icon  = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });

    // ── Provider hint text ─────────────────────────────────────────────────────
    const hints = {
        gemini: 'Get your key at <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.',
        claude: 'Get your key at <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a>.',
        openai: 'Get your key at <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>.',
    };

    function updateHint() {
        const val  = document.getElementById('aiProviderSelect').value;
        document.getElementById('providerHint').innerHTML = hints[val] ?? '';
    }

    document.getElementById('aiProviderSelect').addEventListener('change', updateHint);
    updateHint();

    // ── Test connection ────────────────────────────────────────────────────────
    const testBtn    = document.getElementById('testKeyBtn');
    const testResult = document.getElementById('testResult');

    if (testBtn) {
        testBtn.addEventListener('click', function () {
            testBtn.disabled = true;
            testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
            testResult.style.display = 'none';

            fetch('{{ route("admin.settings.testKey") }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                testResult.style.display = 'block';
                testResult.className = 'mt-3 alert ' + (data.success ? 'alert-success' : 'alert-danger');
                testResult.innerHTML = '<i class="bi bi-' + (data.success ? 'check-circle' : 'x-circle') + '-fill me-2"></i>' + data.message;
            })
            .catch(() => {
                testResult.style.display = 'block';
                testResult.className = 'mt-3 alert alert-danger';
                testResult.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i> Request failed.';
            })
            .finally(() => {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="bi bi-wifi me-1"></i> Test Connection';
            });
        });
    }
});
</script>
@endpush

@endsection