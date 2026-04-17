<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SettingLog;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private SettingService $settings) {}

    // -------------------------------------------------------------------------
    // Show settings page
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $activeTab = $request->input('tab', 'app');

        $groups = [
            'app'         => Setting::where('group', 'app')->orderBy('id')->get(),
            'ai'          => Setting::where('group', 'ai')->orderBy('id')->get(),
            'survey'      => Setting::where('group', 'survey')->orderBy('id')->get(),
            'locale'      => Setting::where('group', 'locale')->orderBy('id')->get(),
            'mail'        => Setting::where('group', 'mail')->orderBy('id')->get(),
            'security'    => Setting::where('group', 'security')->orderBy('id')->get(),
            'maintenance' => Setting::where('group', 'maintenance')->orderBy('id')->get(),
        ];

        return view('admin.settings.index', compact('groups', 'activeTab'));
    }

    // -------------------------------------------------------------------------
    // Save a group — FIXED: uses encoded flat keys, not dot-notation
    // -------------------------------------------------------------------------

    public function update(Request $request, string $group): RedirectResponse
    {
        $allowed = ['app', 'ai', 'survey', 'locale', 'mail', 'security', 'maintenance'];

        if (! in_array($group, $allowed)) {
            abort(404);
        }

        $groupSettings = Setting::where('group', $group)->get()->keyBy('key');

        // ------------------------------------------------------------------
        // Handle file uploads
        // ------------------------------------------------------------------
        foreach ($groupSettings->where('type', 'file') as $setting) {
            // File input names are encoded: "app__logo" for key "app.logo"
            $inputName = $this->encodeKey($setting->key);

            if ($request->hasFile($inputName)) {
                $this->settings->storeFile(
                    $setting->key,
                    $request->file($inputName),
                    'settings'
                );
            }
        }

        // ------------------------------------------------------------------
        // Handle all other settings
        // Keys use encoded names in the form (dots → double underscores)
        // to avoid PHP's dot-to-underscore conversion bug
        // ------------------------------------------------------------------
        $data = [];

        foreach ($groupSettings->where('type', '!=', 'file') as $setting) {
            if ($setting->is_readonly) {
                continue;
            }

            $inputName = $this->encodeKey($setting->key);

            if ($setting->type === 'boolean') {
                // Checkboxes: if not present in POST = unchecked = false
                // The hidden input with value="0" ensures the key is always present
                $data[$setting->key] = $request->boolean($inputName);
            } else {
                // Use get() on raw post data to avoid dot-notation nesting issue
                $raw = $request->post($inputName);

                // Treat empty string as null for nullable fields,
                // but keep "0" and "false" as valid values
                $data[$setting->key] = ($raw === '' || $raw === null) ? null : $raw;
            }
        }

        [$changed, $errors] = $this->settings->setMany($data);

        if (! empty($errors)) {
            return back()
                ->with('error', 'Some settings failed: ' . implode(', ', $errors))
                ->with('tab', $group);
        }

        return redirect()
            ->route('admin.settings.index', ['tab' => $group])
            ->with('success', ucfirst($group) . " settings saved. {$changed} value(s) updated.");
    }

    // -------------------------------------------------------------------------
    // Audit log
    // -------------------------------------------------------------------------

    public function logs(Request $request): View
    {
        $logs = SettingLog::orderByDesc('changed_at')
            ->when($request->input('group'), fn ($q, $g) => $q->where('group', $g))
            ->when($request->input('key'),   fn ($q, $k) => $q->where('key', 'like', "%{$k}%"))
            ->paginate(30)
            ->withQueryString();

        $groups = SettingLog::distinct()->pluck('group')->sort()->values();

        return view('admin.settings.logs', compact('logs', 'groups'));
    }

    // -------------------------------------------------------------------------
    // Connection tests
    // -------------------------------------------------------------------------

    public function testNlp(): \Illuminate\Http\JsonResponse
    {
        $healthy = app(\App\Services\SentimentService::class)->isHealthy();

        return response()->json([
            'ok'      => $healthy,
            'message' => $healthy
                ? '✓ NLP server is reachable.'
                : '✗ NLP server is not responding. Make sure the Flask server is running.',
        ]);
    }

    public function testGemini(): \Illuminate\Http\JsonResponse
    {
        $apiKey = $this->settings->get('ai.gemini_api_key', '');

        if (empty($apiKey)) {
            return response()->json(['ok' => false, 'message' => '✗ No API key configured.']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            $ok = $response->successful();

            return response()->json([
                'ok'      => $ok,
                'message' => $ok
                    ? '✓ Gemini API key is valid.'
                    : '✗ Gemini API returned: HTTP ' . $response->status(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => '✗ ' . $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Cache flush
    // -------------------------------------------------------------------------

    public function clearCache(): RedirectResponse
    {
        $this->settings->flush();

        return back()->with('success', 'Settings cache cleared successfully.');
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Encode a dot-notation key to a safe HTML input name.
     * "app.name" → "app__name"
     * "ai.gemini_api_key" → "ai__gemini_api_key"
     *
     * This avoids PHP converting "app.name" POST key to "app_name",
     * and avoids Laravel treating it as nested array "app → name".
     */
    public static function encodeKey(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    /**
     * Decode back: "app__name" → "app.name"
     */
    public static function decodeKey(string $encodedKey): string
    {
        return str_replace('__', '.', $encodedKey);
    }
}
