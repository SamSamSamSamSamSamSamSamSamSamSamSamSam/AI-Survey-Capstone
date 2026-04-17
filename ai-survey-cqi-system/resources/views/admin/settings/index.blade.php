@extends('layouts.app')
@section('title', 'System Settings')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
</ol>
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-heading">System Settings</h2>
        <p class="page-subheading">Configure application behaviour, AI integrations, and maintenance options.</p>
    </div>
    <a href="{{ route('admin.settings.logs') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-clock-history me-1"></i> Audit Log
    </a>
</div>

<div class="settings-layout">

    {{-- ===== LEFT: Tab Rail ===== --}}
    <div class="settings-rail">

        <div class="settings-rail__header">
            <i class="bi bi-gear-fill me-2"></i> Settings
        </div>

        @php
        $tabs = [
            'app'         => ['icon' => 'bi-building',           'label' => 'Application Identity'],
            'ai'          => ['icon' => 'bi-robot',              'label' => 'AI & NLP'],
            'survey'      => ['icon' => 'bi-clipboard-check',    'label' => 'Survey & Academic'],
            'locale'      => ['icon' => 'bi-globe',              'label' => 'Localization'],
            'mail'        => ['icon' => 'bi-envelope',           'label' => 'Mail & Notifications'],
            'security'    => ['icon' => 'bi-shield-lock',        'label' => 'Security'],
            'maintenance' => ['icon' => 'bi-tools',              'label' => 'Maintenance'],
        ];
        @endphp

        @foreach ($tabs as $tabKey => $tab)
        <button type="button"
                class="settings-rail__tab {{ $activeTab === $tabKey ? 'settings-rail__tab--active' : '' }}"
                data-tab="{{ $tabKey }}">
            <i class="bi {{ $tab['icon'] }} settings-rail__tab-icon"></i>
            {{ $tab['label'] }}
        </button>
        @endforeach

        <div class="settings-rail__footer">
            <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-trash me-1"></i> Clear Cache
                </button>
            </form>
        </div>

    </div>

    {{-- ===== RIGHT: Panels ===== --}}
    <div class="settings-panels">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ─── APP IDENTITY ─── --}}
        <div class="settings-panel {{ $activeTab === 'app' ? 'settings-panel--active' : '' }}"
             id="panel-app">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">Application Identity</h3>
                <p class="settings-panel__desc">
                    Configure how the system identifies itself across pages, emails, and PDF reports.
                </p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update', 'app') }}"
                          enctype="multipart/form-data">
                        @csrf @method('PUT')
                        @foreach ($groups['app'] as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach
                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">Changes take effect immediately.</span>
                            <button type="submit" class="btn btn-primary">
                                Save Application Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── AI & NLP ─── --}}
        <div class="settings-panel {{ $activeTab === 'ai' ? 'settings-panel--active' : '' }}"
             id="panel-ai">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">AI &amp; NLP Configuration</h3>
                <p class="settings-panel__desc">
                    Configure Gemini AI for CQI report generation and the local NLP sentiment server.
                </p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update', 'ai') }}"
                          enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <p class="settings-subsection">Gemini AI (CQI Report Generation)</p>
                        @foreach ($groups['ai']->filter(fn ($s) => str_starts_with($s->key, 'ai.gemini')) as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <div class="settings-test-row">
                            <button type="button" class="settings-test-btn" data-test="gemini">
                                <i class="bi bi-plug me-1"></i> Test Gemini Connection
                            </button>
                            <span class="settings-test-result d-none" id="test-gemini"></span>
                        </div>

                        <p class="settings-subsection">NLP Sentiment Server (Flask)</p>
                        @foreach ($groups['ai']->filter(fn ($s) => str_starts_with($s->key, 'ai.nlp')) as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <div class="settings-test-row">
                            <button type="button" class="settings-test-btn" data-test="nlp">
                                <i class="bi bi-plug me-1"></i> Test NLP Server Connection
                            </button>
                            <span class="settings-test-result d-none" id="test-nlp"></span>
                        </div>

                        <p class="settings-subsection">CQI Report Identity Override</p>
                        @foreach ($groups['ai']->filter(fn ($s) => str_starts_with($s->key, 'ai.cqi')) as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">API keys are masked in the audit log.</span>
                            <button type="submit" class="btn btn-primary">
                                Save AI Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── SURVEY & ACADEMIC ─── --}}
        <div class="settings-panel {{ $activeTab === 'survey' ? 'settings-panel--active' : '' }}"
             id="panel-survey">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">Survey &amp; Academic Logic</h3>
                <p class="settings-panel__desc">
                    Configure grading thresholds, scale settings, and academic term labels.
                </p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" 
                            action="{{ route('admin.settings.update', 'survey') }}"
                            enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <p class="settings-subsection">Rating &amp; Grading Thresholds</p>
                        @foreach ($groups['survey']->whereIn('key', ['survey.passing_threshold','survey.default_scale_max','survey.excellent_threshold','survey.very_good_threshold','survey.good_threshold','survey.fair_threshold']) as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <p class="settings-subsection">Survey Behaviour</p>
                        @foreach ($groups['survey']->whereIn('key', ['survey.allow_anonymous','survey.reminder_days_before']) as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <p class="settings-subsection">Academic Calendar Labels</p>
                        @foreach ($groups['survey']->whereIn('key', ['survey.academic_year_start_month','survey.sem1_label','survey.sem2_label','survey.sem3_label']) as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">Threshold changes affect new CQI reports only.</span>
                            <button type="submit" class="btn btn-primary">
                                Save Survey Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── LOCALIZATION ─── --}}
        <div class="settings-panel {{ $activeTab === 'locale' ? 'settings-panel--active' : '' }}"
             id="panel-locale">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">Localization &amp; Regional Settings</h3>
                <p class="settings-panel__desc">
                    Configure timezone, date/time formats, and regional preferences.
                </p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" 
                            action="{{ route('admin.settings.update', 'locale') }}"
                            enctype="multipart/form-data">
                        @csrf @method('PUT')
                        @foreach ($groups['locale'] as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach

                        <div class="settings-time-preview">
                            <i class="bi bi-clock me-2 text-muted"></i>
                            <strong>Current time preview:</strong>
                            <span class="ms-2">
                                {{ now()->format(setting('locale.date_format', 'M d, Y') . ' ' . setting('locale.time_format', 'h:i A')) }}
                            </span>
                        </div>

                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">Timezone changes affect all date displays immediately.</span>
                            <button type="submit" class="btn btn-primary">
                                Save Locale Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── MAIL ─── --}}
        <div class="settings-panel {{ $activeTab === 'mail' ? 'settings-panel--active' : '' }}"
             id="panel-mail">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">Mail &amp; Notifications</h3>
                <p class="settings-panel__desc">
                    Configure outgoing email identity and notification toggles.
                    The mail driver is set in <code>.env</code>.
                </p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" 
                            action="{{ route('admin.settings.update', 'mail') }}"
                            enctype="multipart/form-data">
                        @csrf @method('PUT')
                        @foreach ($groups['mail'] as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach
                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">Sender identity changes apply to all future emails.</span>
                            <button type="submit" class="btn btn-primary">
                                Save Mail Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── SECURITY ─── --}}
        <div class="settings-panel {{ $activeTab === 'security' ? 'settings-panel--active' : '' }}"
             id="panel-security">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">Security Settings</h3>
                <p class="settings-panel__desc">
                    Control session behaviour, login protection, and authentication requirements.
                </p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" 
                            action="{{ route('admin.settings.update', 'security') }}"
                            enctype="multipart/form-data">
                        @csrf @method('PUT')
                        @foreach ($groups['security'] as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach
                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">Session changes apply to new logins only.</span>
                            <button type="submit" class="btn btn-primary">
                                Save Security Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── MAINTENANCE ─── --}}
        <div class="settings-panel {{ $activeTab === 'maintenance' ? 'settings-panel--active' : '' }}"
             id="panel-maintenance">
            <div class="settings-panel__header">
                <h3 class="settings-panel__title">Maintenance &amp; Announcements</h3>
                <p class="settings-panel__desc">
                    Control system availability and communicate with all users via banners.
                </p>
            </div>

            @if (setting('maintenance.mode'))
                <div class="survey-banner survey-banner--inactive mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Maintenance mode is currently ON.</strong>
                    Only administrators can access the system.
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" 
                            action="{{ route('admin.settings.update', 'maintenance') }}"
                            enctype="multipart/form-data">
                        @csrf @method('PUT')
                        @foreach ($groups['maintenance'] as $s)
                            @include('admin.settings._setting_row', ['s' => $s])
                        @endforeach
                        <div class="settings-save-bar">
                            <span class="settings-save-bar__hint">Maintenance mode takes effect immediately after save.</span>
                            <button type="submit" class="btn btn-primary">
                                Save Maintenance Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /.settings-panels --}}

</div>{{-- /.settings-layout --}}

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ---- Tab switching ----
    const tabs   = document.querySelectorAll('.settings-rail__tab');
    const panels = document.querySelectorAll('.settings-panel');

    function switchTab(key) {
        tabs.forEach(t => t.classList.toggle('settings-rail__tab--active', t.dataset.tab === key));
        panels.forEach(p => p.classList.toggle('settings-panel--active', p.id === 'panel-' + key));
        history.replaceState(null, '', '?tab=' + key);
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { switchTab(this.dataset.tab); });
    });

    // ---- Connection test buttons ----
    document.querySelectorAll('.settings-test-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const type     = this.dataset.test;
            const resultEl = document.getElementById('test-' + type);
            if (!resultEl) return;

            resultEl.className = 'settings-test-result';
            resultEl.textContent = 'Testing…';
            resultEl.classList.remove('d-none');

            fetch(`/admin/settings/test-${type}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            })
            .then(r => r.json())
            .then(function (data) {
                resultEl.textContent = data.message;
                resultEl.classList.add(data.ok ? 'settings-test-result--ok' : 'settings-test-result--err');
            })
            .catch(function () {
                resultEl.textContent = 'Request failed.';
                resultEl.classList.add('settings-test-result--err');
            });
        });
    });

    // ---- File input label update ----
    document.querySelectorAll('.setting-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const label = this.closest('.setting-file-wrap')?.querySelector('.setting-file-label');
            if (label && this.files.length) {
                label.innerHTML = `<i class="bi bi-paperclip me-1"></i> ${this.files[0].name}`;
            }
        });
    });

})();
</script>
@endpush