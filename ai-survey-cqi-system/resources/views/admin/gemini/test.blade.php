<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        
    </style>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="page-header1">
    <div class="page-header-content">
        <h1 class="page-title">Gemini API Diagnostics</h1>
        <p class="page-subtitle">Tests the live connection to Google Gemini. Sends a minimal prompt and inspects the response.</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('admin.gemini.test') }}" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise me-1"></i> Run Test Again
        </a>
    </div>
</div>

{{-- ── Status Banner ────────────────────────────────────────────────────────── --}}
<div class="gemini-status-banner gemini-status-banner--{{ $result['status'] }} mb-4">
    <div class="gemini-status-icon">
        @if($result['status'] === 'ok')
            <i class="bi bi-check-circle-fill"></i>
        @elseif($result['status'] === 'misconfigured')
            <i class="bi bi-key-fill"></i>
        @else
            <i class="bi bi-x-circle-fill"></i>
        @endif
    </div>
    <div class="gemini-status-body">
        @if($result['status'] === 'ok')
            <strong>Connected</strong> — Gemini API is reachable and responding correctly.
        @elseif($result['status'] === 'misconfigured')
            <strong>Not Configured</strong> — API key is missing from <code>.env</code>.
        @else
            <strong>Failed</strong> — Could not get a valid response from Gemini.
        @endif
    </div>
    @if($result['latency_ms'])
        <div class="gemini-status-latency">
            <i class="bi bi-speedometer2 me-1"></i>{{ $result['latency_ms'] }} ms
        </div>
    @endif
</div>

<div class="row g-4">

    {{-- ── Connection Details ───────────────────────────────────────────────── --}}
    <div class="col-md-5">
        <div class="gemini-card">
            <div class="gemini-card-header">
                <i class="bi bi-info-circle me-2"></i>Connection Details
            </div>
            <div class="gemini-card-body">
                <table class="gemini-detail-table">
                    <tr>
                        <td class="label">Model</td>
                        <td><code>{{ $result['model'] }}</code></td>
                    </tr>
                    <tr>
                        <td class="label">HTTP Status</td>
                        <td>
                            @if($result['http_code'])
                                <span class="badge {{ $result['http_code'] === 200 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $result['http_code'] }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Latency</td>
                        <td>
                            @if($result['latency_ms'])
                                {{ $result['latency_ms'] }} ms
                                @if($result['latency_ms'] < 2000)
                                    <span class="text-success small ms-1"><i class="bi bi-lightning-charge-fill"></i> Fast</span>
                                @elseif($result['latency_ms'] < 5000)
                                    <span class="text-warning small ms-1"><i class="bi bi-clock"></i> Moderate</span>
                                @else
                                    <span class="text-danger small ms-1"><i class="bi bi-exclamation-triangle"></i> Slow</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">API Key</td>
                        <td>
                            @if($result['key_preview'])
                                <code>{{ $result['key_preview'] }}</code>
                                <span class="text-success ms-1"><i class="bi bi-check-circle"></i> Set</span>
                            @else
                                <span class="text-danger"><i class="bi bi-x-circle"></i> Not set</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if($result['error_detail'])
                    <div class="gemini-error-box mt-3">
                        <div class="gemini-error-label"><i class="bi bi-bug me-1"></i>Error Detail</div>
                        <div class="gemini-error-text">{{ $result['error_detail'] }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Prompt & Response ────────────────────────────────────────────────── --}}
    <div class="col-md-7">
        <div class="gemini-card">
            <div class="gemini-card-header">
                <i class="bi bi-chat-left-text me-2"></i>Prompt Sent
            </div>
            <div class="gemini-card-body">
                <pre class="gemini-code-block">{{ $result['prompt_sent'] }}</pre>
            </div>
        </div>

        @if($result['parsed_text'])
        <div class="gemini-card mt-3">
            <div class="gemini-card-header gemini-card-header--success">
                <i class="bi bi-chat-right-text me-2"></i>Gemini Response (Parsed Text)
            </div>
            <div class="gemini-card-body">
                <pre class="gemini-code-block gemini-code-block--response">{{ $result['parsed_text'] }}</pre>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Raw JSON Response ────────────────────────────────────────────────── --}}
    @if($result['raw_response'])
    <div class="col-12">
        <div class="gemini-card">
            <div class="gemini-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-braces me-2"></i>Full Raw JSON Response</span>
                <button class="btn btn-sm btn-outline-secondary"
                        onclick="toggleRaw()"
                        id="toggleRawBtn">
                    <i class="bi bi-eye me-1"></i>Show
                </button>
            </div>
            <div class="gemini-card-body" id="rawJsonBlock" style="display:none;">
                <pre class="gemini-code-block gemini-code-block--raw">{{ json_encode($result['raw_response'], JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Troubleshooting Guide ────────────────────────────────────────────── --}}
    @if($result['status'] !== 'ok')
    <div class="col-12">
        <div class="gemini-card gemini-card--warning">
            <div class="gemini-card-header">
                <i class="bi bi-wrench me-2"></i>Troubleshooting
            </div>
            <div class="gemini-card-body">
                <ul class="gemini-troubleshoot-list">
                    @if($result['status'] === 'misconfigured')
                        <li>Open your <code>.env</code> file and add: <code>GEMINI_API_KEY=your_key_here</code></li>
                        <li>Run <code>php artisan config:clear</code> after editing <code>.env</code></li>
                        <li>Get a free API key at <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a></li>
                    @elseif($result['http_code'] === 400)
                        <li>HTTP 400 means the request body was malformed — check the model name in <code>GeminiCQIService.php</code></li>
                    @elseif($result['http_code'] === 401 || $result['http_code'] === 403)
                        <li>HTTP {{ $result['http_code'] }} means your API key is invalid or has no permissions</li>
                        <li>Verify the key at <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a></li>
                        <li>Make sure the Generative Language API is enabled in your Google Cloud project</li>
                    @elseif($result['http_code'] === 429)
                        <li>HTTP 429 means you've hit the rate limit — wait a moment and try again</li>
                        <li>Free tier: 15 requests/minute on Gemini 1.5 Flash</li>
                    @else
                        <li>Run <code>php artisan config:clear && php artisan cache:clear</code></li>
                        <li>Check <code>storage/logs/laravel.log</code> for more detail</li>
                        <li>Verify your server can reach external URLs (check firewall/proxy settings)</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif

</div>
</body>

<script>
    function toggleRaw() {
        const block = document.getElementById('rawJsonBlock');
        const btn   = document.getElementById('toggleRawBtn');
        const shown = block.style.display !== 'none';
        block.style.display = shown ? 'none' : 'block';
        btn.innerHTML = shown
            ? '<i class="bi bi-eye me-1"></i>Show'
            : '<i class="bi bi-eye-slash me-1"></i>Hide';
    }
</script>

</html>