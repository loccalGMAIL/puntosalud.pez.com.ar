<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SettingService
{
    private const TTL = 300; // 5 minutos

    /**
     * Keys que se guardan encriptadas en la base de datos.
     * El encriptado/desencriptado es transparente para los consumidores.
     */
    private const ENCRYPTED_KEYS = [
        'whatsapp.api_key',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", self::TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            $value = $setting?->value ?? $default;

            if ($value !== null && $value !== '' && in_array($key, self::ENCRYPTED_KEYS, true)) {
                try {
                    $value = Crypt::decryptString($value);
                } catch (DecryptException) {
                    // Valor legacy guardado en texto plano: se usa tal cual
                    // (se re-encripta automáticamente en el próximo set())
                }
            }

            return $value;
        });
    }

    public function set(string $key, string $group, mixed $value): void
    {
        if ($value !== null && $value !== '' && in_array($key, self::ENCRYPTED_KEYS, true)) {
            $value = Crypt::encryptString($value);
        }

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
