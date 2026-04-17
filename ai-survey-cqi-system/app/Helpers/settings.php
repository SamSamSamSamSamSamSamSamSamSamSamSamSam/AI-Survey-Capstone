<?php
// app/Helpers/settings.php
// Auto-loaded via composer.json autoload.files

if (! function_exists('setting')) {
    /**
     * Get a system setting value by key.
     *
     * Usage:
     *   setting('app.name')                    // returns value or null
     *   setting('app.name', 'Default Name')    // returns value or default
     *   setting()                              // returns the SettingService instance
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $service = app(\App\Services\SettingService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}
