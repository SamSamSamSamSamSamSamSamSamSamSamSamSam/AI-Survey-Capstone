<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SettingLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    private const CACHE_TTL    = 3600;       // 1 hour
    private const CACHE_PREFIX = 'setting:';
    private const GROUP_CACHE  = 'settings:group:';

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Get a setting value by key, cast to its declared type.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->cast_value ?? $default;
        });
    }

    /**
     * Get all settings in a group, keyed by setting key.
     */
    public function getGroup(string $group): array
    {
        return Cache::remember(self::GROUP_CACHE . $group, self::CACHE_TTL, function () use ($group) {
            return Setting::where('group', $group)
                ->get()
                ->mapWithKeys(fn ($s) => [$s->key => $s->cast_value])
                ->toArray();
        });
    }

    /**
     * Get all settings as a flat key => value array.
     */
    public function all(): array
    {
        return Cache::remember('settings:all', self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn ($s) => [$s->key => $s->cast_value])
                ->toArray();
        });
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    /**
     * Set a single setting value with audit logging.
     */
    public function set(string $key, mixed $value, bool $log = true): bool
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting || $setting->is_readonly) {
            return false;
        }

        $oldRaw = $setting->value;
        $newRaw = Setting::castForStorage($setting->type, $value);

        if ($oldRaw === $newRaw) {
            return true; // no change
        }

        $setting->update(['value' => $newRaw]);

        // Bust cache
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::GROUP_CACHE . $setting->group);
        Cache::forget('settings:all');

        // Audit log
        if ($log) {
            $this->writeLog($setting, $oldRaw, $newRaw);
        }

        return true;
    }

    /**
     * Set multiple settings at once (used by the form controller).
     * Returns [changed_count, errors[]]
     */
    public function setMany(array $data, bool $log = true): array
    {
        $changed = 0;
        $errors  = [];

        foreach ($data as $key => $value) {
            try {
                if ($this->set($key, $value, $log)) {
                    $changed++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Failed to save '{$key}': " . $e->getMessage();
                Log::error("SettingService::setMany error for key {$key}", ['error' => $e->getMessage()]);
            }
        }

        return [$changed, $errors];
    }

    // -------------------------------------------------------------------------
    // File upload (logo / favicon)
    // -------------------------------------------------------------------------

    /**
     * Store an uploaded file and update the related setting key.
     * Returns the public path or null on failure.
     */
    public function storeFile(string $key, \Illuminate\Http\UploadedFile $file, string $directory = 'settings'): ?string
    {
        $setting = Setting::where('key', $key)->where('type', 'file')->first();

        if (! $setting) {
            return null;
        }

        // Delete old file
        if ($setting->value && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
        }

        $path = $file->store($directory, 'public');

        $this->set($key, $path);

        return $path;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function flush(): void
    {
        Cache::flush();
    }

    private function writeLog(Setting $setting, ?string $oldRaw, string $newRaw): void
    {
        $user = Auth::user();

        SettingLog::create([
            'key'              => $setting->key,
            'group'            => $setting->group,
            'old_value'        => $setting->is_sensitive ? '••••••' : $oldRaw,
            'new_value'        => $setting->is_sensitive ? '••••••' : $newRaw,
            'changed_by_name'  => $user?->name ?? 'System',
            'changed_by_id'    => $user?->id,
            'changed_at'       => now(),
        ]);
    }
}
