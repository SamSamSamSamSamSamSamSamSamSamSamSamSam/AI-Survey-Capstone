<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'institution_name'        => Setting::get('institution_name'),
            'department_name'         => Setting::get('department_name'),
            'target_rating'           => Setting::get('target_rating'),
            'cqi_priority_high'       => Setting::get('cqi_priority_high'),
            'cqi_priority_medium'     => Setting::get('cqi_priority_medium'),
            'min_responses_threshold' => Setting::get('min_responses_threshold'),
            'ai_provider'             => Setting::get('ai_provider'),
            'ai_api_key_set'          => Setting::hasApiKey(),
            'report_title_prefix'     => Setting::get('report_title_prefix'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'institution_name'        => 'required|string|max:255',
            'department_name'         => 'required|string|max:255',
            'target_rating'           => 'required|numeric|min:1|max:5',
            'cqi_priority_high'       => 'required|numeric|min:0|max:5',
            'cqi_priority_medium'     => 'required|numeric|min:0|max:5',
            'min_responses_threshold' => 'required|integer|min:1',
            'ai_provider'             => 'required|in:gemini,claude,openai',
            'ai_api_key'              => 'nullable|string|max:500',
            'report_title_prefix'     => 'required|string|max:255',
        ]);

        $keys = [
            'institution_name', 'department_name', 'target_rating',
            'cqi_priority_high', 'cqi_priority_medium', 'min_responses_threshold',
            'ai_provider', 'report_title_prefix',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        if ($request->filled('ai_api_key')) {
            Setting::setApiKey($request->input('ai_api_key'));
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function clearApiKey()
    {
        Setting::set('ai_api_key', '');
        return back()->with('success', 'API key removed.');
    }

    public function testApiKey()
    {
        $provider = Setting::get('ai_provider');
        $key      = Setting::getApiKey();

        if (empty($key)) {
            return response()->json(['success' => false, 'message' => 'No API key configured.']);
        }

        try {
            if ($provider === 'gemini') {
                $response = Http::timeout(10)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$key}",
                    ['contents' => [['role' => 'user', 'parts' => [['text' => 'Reply with: OK']]]]]
                );
                $success = $response->successful();
                $message = $success ? 'Gemini connection successful.' : 'Gemini returned an error: ' . $response->status();

            } elseif ($provider === 'claude') {
                $response = Http::timeout(10)->withHeaders([
                    'x-api-key'         => $key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 10,
                    'messages'   => [['role' => 'user', 'content' => 'Reply with: OK']],
                ]);
                $success = $response->successful();
                $message = $success ? 'Claude connection successful.' : 'Claude returned an error: ' . $response->status();

            } elseif ($provider === 'openai') {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => "Bearer {$key}",
                    'Content-Type'  => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model'      => 'gpt-4o-mini',
                    'max_tokens' => 10,
                    'messages'   => [['role' => 'user', 'content' => 'Reply with: OK']],
                ]);
                $success = $response->successful();
                $message = $success ? 'OpenAI connection successful.' : 'OpenAI returned an error: ' . $response->status();

            } else {
                return response()->json(['success' => false, 'message' => 'Unknown provider.']);
            }

            return response()->json(['success' => $success, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
    }
}