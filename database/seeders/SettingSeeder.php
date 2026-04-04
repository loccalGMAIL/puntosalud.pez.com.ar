<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'center_name',     'value' => 'Centro de Atención Médica'],
            ['key' => 'center_subtitle', 'value' => ''],
            ['key' => 'center_address',  'value' => 'Tucumán 925, Cosquín'],
            ['key' => 'center_phone',    'value' => '(3541) 705-281'],
            ['key' => 'center_email',    'value' => 'puntosalud94@gmail.com'],
            ['key' => 'center_active',   'value' => '1'],
        ];

        foreach ($settings as $item) {
            Setting::firstOrCreate(
                ['key' => $item['key']],
                ['group' => 'center', 'value' => $item['value']]
            );
        }
    }
}
