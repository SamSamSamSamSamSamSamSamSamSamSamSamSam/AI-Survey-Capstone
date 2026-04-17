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
    // Show settings page (all groups)
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
    // Save a group of settings
    // -------------------------------------------------------------------------

    public function update(Request $request, string $group): RedirectResponse
    {
        $allowed = ['app', 'ai', 'survey', 'locale', 'mail', 'security', 'maintenance'];

        if (! in_array($group, $allowed)) {
            abort(404);
        }

        $groupSettings = Setting::where('group', $group)->get()->keyBy('key');

        // Handle file uploads first
        foreach ($groupSettings->where('type', 'file') as $setting) {
            if ($request->hasFile($setting->key)) {
                $this->settings->storeFile(
                    $setting->key,
                    $request->file($setting->key),
                    'settings'
                );
            }
        }

        // Handle all other settings
        $data = [];
        foreach ($groupSettings->where('type', '!=', 'file') as $setting) {
            if ($setting->is_readonly) continue;

            $key = $setting->key;

            if ($setting->type === 'boolean') {
                $data[$key] = $request->boolean($key);
            } else {
                $data[$key] = $request->input($key);
            }
        }

        [$changed, $errors] = $this->settings->setMany($data);

        if (! empty($errors)) {
            return back()->with('error', 'Some settings failed to save: ' . implode(', ', $errors))
                         ->with('tab', $group);
        }

        return redirect()->route('admin.settings.index', ['tab' => $group])
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
    // Test connections
    // -------------------------------------------------------------------------

    public function testNlp(): \Illuminate\Http\JsonResponse
    {
        $healthy = app(\App\Services\SentimentService::class)->isHealthy();
        return response()->json([
            'ok'      => $healthy,
            'message' => $healthy ? 'NLP server is reachable.' : 'NLP server is not responding.',
        ]);
    }

    public function testGemini(): \Illuminate\Http\JsonResponse
    {
        try {
            $service  = app(\App\Services\GeminiService::class);
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models?key=" . setting('ai.gemini_api_key', ''));

            $ok = $response->ok() || $response->status() === 200;
            return response()->json([
                'ok'      => $ok,
                'message' => $ok ? 'Gemini API key is valid.' : 'Gemini API key is invalid or quota exceeded.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Cache flush
    // -------------------------------------------------------------------------

    public function clearCache(): RedirectResponse
    {
        $this->settings->flush();

        return back()->with('success', 'Settings cache cleared.');
    }
}
