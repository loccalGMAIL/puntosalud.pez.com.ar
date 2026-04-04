<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('center_image')) {
    function center_image(string $name, string $fallback = ''): string
    {
        $extensions = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
        foreach ($extensions as $ext) {
            $path = public_path("center/{$name}.{$ext}");
            if (file_exists($path)) {
                return asset("center/{$name}.{$ext}") . '?v=' . filemtime($path);
            }
        }
        return $fallback ? asset($fallback) : '';
    }
}
