<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    // ── Default values ─────────────────────────────────────────────────────────

    const DEFAULTS = [
        'institution_name'        => 'DCISM',
        'department_name'         => 'Department of Computer and Information Sciences and Mathematics',
        'target_rating'           => '4.0',
        'cqi_priority_high'       => '1.80',
        'cqi_priority_medium'     => '1.60',
        'min_responses_threshold' => '3',
        'ai_provider'             => 'gemini',
        'ai_api_key'              => '',
        'report_title_prefix'     => 'CQI Summary Report',
    ];

    // ── Get a setting value with fallback to default ───────────────────────────

    public static function get(string $key, $default = null): mixed
    {
        $cached = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->value('value');
        });

        if ($cached === null) {
            return $default ?? (static::DEFAULTS[$key] ?? null);
        }

        return $cached;
    }

    // ── Set a value, flushing its cache entry ──────────────────────────────────

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting_{$key}");
    }

    // ── Store API key encrypted ────────────────────────────────────────────────

    public static function setApiKey(string $value): void
    {
        $encrypted = empty($value) ? '' : Crypt::encryptString($value);
        static::set('ai_api_key', $encrypted);
    }

    // ── Retrieve API key decrypted ─────────────────────────────────────────────

    public static function getApiKey(): string
    {
        $value = static::get('ai_api_key', '');

        if (empty($value)) return '';

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return '';
        }
    }

    // ── Check if an AI key is configured ──────────────────────────────────────

    public static function hasApiKey(): bool
    {
        return !empty(static::get('ai_api_key', ''));
    }
}