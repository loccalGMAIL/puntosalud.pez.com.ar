<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class WhatsAppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'whatsapp.enabled'      => '0',
            'whatsapp.api_url'      => '',
            'whatsapp.api_key'      => '',
            'whatsapp.instance'     => '',
            'whatsapp.hours_before' => '24',
            'whatsapp.template'     => 'Hola {{nombre}}, le recordamos su turno el {{fecha}} a las {{hora}} con {{profesional}}. Por favor confirme su asistencia respondiendo SI o NO.',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['group' => 'whatsapp', 'value' => $value]
            );
        }
    }
}
