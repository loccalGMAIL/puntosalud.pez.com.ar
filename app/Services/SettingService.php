<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const TTL = 300; // 5 minutos

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", self::TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    public function set(string $key, string $group, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => $value]
        );
        Cache::forget("setting:{$key}");
    }

    public function clearCache(): void
    {
        Cache::flush();
    }
}
