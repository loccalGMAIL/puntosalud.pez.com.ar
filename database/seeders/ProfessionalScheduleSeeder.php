<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use Illuminate\Database\Seeder;

class ProfessionalScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $professionals = Professional::all();
        $offices = Office::where('is_active', true)->get();

        // Crear horarios para cada profesional activo
        foreach ($professionals->where('is_active', true) as $professional) {
            $this->createSchedulesForProfessional($professional, $offices);
        }
    }

    private function createSchedulesForProfessional($professional, $offices)
    {
        $schedules = [];

        switch ($professional->id) {
            case 1: // Juan Pérez García - Clínica Médica
                $schedules = [
                    ['day' => 1, 'start' => '08:00', 'end' => '12:00', 'office' => 1],
                    ['day' => 3, 'start' => '14:00', 'end' => '18:00', 'office' => 1],
                    ['day' => 5, 'start' => '08:00', 'end' => '12:00', 'office' => 2],
                ];
                break;

            case 2: // María González López - Cardiología
                $schedules = [
                    ['day' => 2, 'start' => '09:00', 'end' => '13:00', 'office' => 2],
                    ['day' => 4, 'start' => '15:00', 'end' => '19:00', 'office' => 2],
                    ['day' => 6, 'start' => '08:00', 'end' => '12:00', 'office' => 3],
                ];
                break;

            case 3: // Carlos Martínez Silva - Dermatología
                $schedules = [
                    ['day' => 1, 'start' => '14:00', 'end' => '18:00', 'office' => 3],
                    ['day' => 3, 'start' => '08:00', 'end' => '12:00', 'office' => 3],
                    ['day' => 5, 'start' => '14:00', 'end' => '18:00', 'office' => 4],
                ];
                break;

            case 4: // Ana Rodríguez Méndez - Clínica Médica
                $schedules = [
                    ['day' => 2, 'start' => '07:30', 'end' => '11:30', 'office' => 1],
                    ['day' => 4, 'start' => '08:00', 'end' => '12:00', 'office' => 4],
                    ['day' => 6, 'start' => '14:00', 'end' => '17:00', 'office' => 1],
                ];
                break;

            default:
                // Horario por defecto para otros profesionales
                $schedules = [
                    ['day' => 1, 'start' => '09:00', 'end' => '13:00', 'office' => 1],
                    ['day' => 3, 'start' => '15:00', 'end' => '19:00', 'office' => 2],
                ];
                break;
        }

        foreach ($schedules as $schedule) {
            // Verificar que el consultorio exista
            $officeId = $offices->where('id', $schedule['office'])->first()?->id;

            ProfessionalSchedule::create([
                'professional_id' => $professional->id,
                'day_of_week' => $schedule['day'],
                'start_time' => $schedule['start'],
                'end_time' => $schedule['end'],
                'office_id' => $officeId,
                'is_active' => true,
            ]);
        }
    }
}
