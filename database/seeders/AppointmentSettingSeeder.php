<?php

namespace Database\Seeders;

use App\Models\AppointmentSetting;
use App\Models\Professional;
use Illuminate\Database\Seeder;

class AppointmentSettingSeeder extends Seeder
{
    public function run(): void
    {
        $professionals = Professional::where('is_active', true)->get();

        foreach ($professionals as $professional) {
            // Duración por defecto según especialidad
            $defaultDuration = $this->getDefaultDurationBySpecialty($professional->specialty_id);

            AppointmentSetting::create([
                'professional_id' => $professional->id,
                'default_duration_minutes' => $defaultDuration,
            ]);
        }
    }

    private function getDefaultDurationBySpecialty($specialtyId)
    {
        $durations = [
            1 => 30, // Clínica Médica - 30 minutos
            2 => 45, // Cardiología - 45 minutos
            3 => 30, // Dermatología - 30 minutos
            4 => 60, // Traumatología - 60 minutos
        ];

        return $durations[$specialtyId] ?? 30;
    }
}
