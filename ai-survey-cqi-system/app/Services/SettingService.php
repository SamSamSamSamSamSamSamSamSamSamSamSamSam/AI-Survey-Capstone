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
    private const CACHE_TTL    = 3600;
    private const CACHE_PREFIX = 'setting:';
    private const GROUP_CACHE  = 'settings:group:';

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting || $setting->value === null) {
                return $default;
            }

            return $this->castValue($setting->type, $setting->value, $setting->is_sensitive) ?? $default;
        });
    }

    public function getGroup(string $group): array
    {
        return Cache::remember(self::GROUP_CACHE . $group, self::CACHE_TTL, function () use ($group) {
            return Setting::where('group', $group)
                ->get()
                ->mapWithKeys(fn ($s) => [$s->key => $this->castValue($s->type, $s->value)])
                ->toArray();
        });
    }

    public function all(): array
    {
        return Cache::remember('settings:all', self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn ($s) => [$s->key => $this->castValue($s->type, $s->value)])
                ->toArray();
        });
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    /**
     * Set a single setting. Returns true if saved, false if skipped/readonly.
     */
    public function set(string $key, mixed $value, bool $log = true): bool
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting || $setting->is_readonly) {
            return false;
        }

        $oldRaw = $setting->value;
        $newRaw = $this->castForStorage($setting->type, $value, $setting->is_sensitive);

        // Only update if value actually changed
        if ($oldRaw === $newRaw) {
            return true;
        }

        $setting->update(['value' => $newRaw]);

        // Bust all related caches
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::GROUP_CACHE . $setting->group);
        Cache::forget('settings:all');

        if ($log) {
            $this->writeLog($setting, $oldRaw, $newRaw);
        }

        return true;
    }

    /**
     * Set multiple settings at once.
     * $data = ['setting.key' => value, ...]
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
                $errors[] = "'{$key}': " . $e->getMessage();
                Log::error("SettingService::setMany — key={$key}", ['error' => $e->getMessage()]);
            }
        }

        return [$changed, $errors];
    }

    // -------------------------------------------------------------------------
    // File upload
    // -------------------------------------------------------------------------

    public function storeFile(string $key, \Illuminate\Http\UploadedFile $file, string $directory = 'settings'): ?string
    {
        $setting = Setting::where('key', $key)->where('type', 'file')->first();

        if (! $setting) {
            return null;
        }

        // Delete old file if exists
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
        // Clear all setting-related cache keys
        Setting::pluck('key')->each(fn ($k) => Cache::forget(self::CACHE_PREFIX . $k));
        Setting::distinct()->pluck('group')->each(fn ($g) => Cache::forget(self::GROUP_CACHE . $g));
        Cache::forget('settings:all');
    }

    /**
     * Cast a stored string value to its PHP type.
     */
    private function castValue(string $type, ?string $value, bool $isSensitive = false): mixed
    {
        if ($value === null) return null;

        // Decrypt if sensitive
        $rawValue = $value;
        if ($isSensitive) {
            try {
                $rawValue = decrypt($value);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Fallback if the value in DB wasn't encrypted yet
                $rawValue = $value;
            }
        }

        return match ($type) {
            'boolean' => in_array($rawValue, ['1', 'true', 'yes', 'on'], true),
            'integer' => (int) $rawValue,
            'json'    => json_decode($rawValue, true),
            default   => $rawValue,
        };
    }

    /**
     * Cast a PHP value to a string for DB storage.
     */
    private function castForStorage(string $type, mixed $value, bool $isSensitive = false): ?string
    {
        if ($value === null || $value === '') {
            if ($type === 'boolean') return '0';
            return null;
        }

        // Step 1: Handle standard casting (JSON, Boolean, etc.)
        $processed = match ($type) {
            'boolean' => ($value && $value !== '0' && $value !== 'false') ? '1' : '0',
            'json'    => is_string($value) ? $value : json_encode($value),
            default   => (string) $value,
        };

        // Step 2: NEW ENCRYPTION LOGIC
        // If the database column 'is_sensitive' is true, encrypt the string
        if ($isSensitive && $processed !== null) {
            return encrypt($processed);
        }

        return $processed;
    }

    private function writeLog(Setting $setting, ?string $oldRaw, ?string $newRaw): void
    {
        $user = Auth::user();

        $processMask = function(?string $val) use ($setting) {
            if (!$setting->is_sensitive || !$val || $val === '(empty)') {
                return $val;
            }

            try {
                // Attempt to decrypt so we can mask the REAL key
                $decrypted = decrypt($val);
                return '••••••••' . mb_substr($decrypted, -4);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Fallback for values not yet encrypted
                return '••••••••' . mb_substr($val, -4);
            }
        };

        SettingLog::create([
            'key'             => $setting->key,
            'group'           => $setting->group,
            'old_value'       => $processMask($oldRaw) ?? '(empty)',
            'new_value'       => $processMask($newRaw) ?? '(empty)',
            'changed_by_name' => $user?->name ?? 'System',
            'changed_by_id'   => $user?->id,
            'changed_at'      => now(),
        ]);
    }
}
