<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Patient;
use App\Models\Office;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $professionals = Professional::where('is_active', true)->get();
        $patients = Patient::all();
        $offices = Office::where('is_active', true)->get();

        // Crear citas para las próximas 2 semanas
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addWeeks(2);

        $appointments = [];

        // Citas pasadas (últimos 7 días)
        for ($i = 7; $i > 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $appointments = array_merge($appointments, $this->createAppointmentsForDate($date, $professionals, $patients, $offices, true));
        }

        // Citas futuras (próximos 14 días)
        for ($i = 0; $i < 14; $i++) {
            $date = Carbon::today()->addDays($i);
            // Solo días laborables (lunes a sábado)
            if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 6) {
                $appointments = array_merge($appointments, $this->createAppointmentsForDate($date, $professionals, $patients, $offices, false));
            }
        }

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }
    }

    private function createAppointmentsForDate($date, $professionals, $patients, $offices, $isPast = false)
    {
        $appointments = [];
        $dayOfWeek = $date->dayOfWeekIso; // 1=Monday, 7=Sunday

        // Solo crear citas para días laborables
        if ($dayOfWeek > 6) {
            return $appointments;
        }

        // Obtener profesionales que trabajan este día (cargar relación schedules)
        $workingProfessionals = $professionals->filter(function ($professional) use ($dayOfWeek) {
            // Cargar schedules si no están cargados
            if (!$professional->relationLoaded('schedules')) {
                $professional->load('schedules');
            }
            return $professional->schedules->where('day_of_week', $dayOfWeek)->where('is_active', true)->isNotEmpty();
        });

        foreach ($workingProfessionals as $professional) {
            $schedules = $professional->schedules->where('day_of_week', $dayOfWeek)->where('is_active', true);
            
            foreach ($schedules as $schedule) {
                // Crear 2-4 citas por horario
                $appointmentCount = rand(2, 4);
                
                for ($i = 0; $i < $appointmentCount; $i++) {
                    $patient = $patients->random();
                    
                    // Generar hora aleatoria dentro del horario del profesional
                    $startHour = Carbon::parse($schedule->start_time)->hour;
                    $endHour = Carbon::parse($schedule->end_time)->hour;
                    $hour = rand($startHour, $endHour - 1);
                    $minute = [0, 15, 30, 45][rand(0, 3)]; // Cada 15 minutos
                    
                    $appointmentDateTime = $date->copy()->setTime($hour, $minute);
                    
                    // Duración aleatoria entre 30 y 60 minutos
                    $duration = [30, 45, 60][rand(0, 2)];
                    
                    // Monto estimado según especialidad
                    $estimatedAmount = $this->getEstimatedAmountBySpecialty($professional->specialty_id);
                    
                    // Estado según si es pasado o futuro
                    if ($isPast) {
                        $status = ['attended', 'attended', 'attended', 'absent', 'cancelled'][rand(0, 4)];
                    } else {
                        $status = $appointmentDateTime->isPast() ? 'attended' : 'scheduled';
                    }

                    // Monto final solo para citas atendidas
                    $finalAmount = $status === 'attended' ? $estimatedAmount + rand(-500, 1000) : null;
                    
                    $appointments[] = [
                        'professional_id' => $professional->id,
                        'patient_id' => $patient->id,
                        'office_id' => $schedule->office_id,
                        'appointment_date' => $appointmentDateTime->format('Y-m-d H:i:s'),
                        'duration' => $duration,
                        'status' => $status,
                        'estimated_amount' => $estimatedAmount,
                        'final_amount' => $finalAmount,
                        'notes' => $this->getRandomNotes(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $appointments;
    }

    private function getEstimatedAmountBySpecialty($specialtyId)
    {
        $amounts = [
            1 => 12000, // Clínica Médica
            2 => 18000, // Cardiología
            3 => 15000, // Dermatología
            4 => 20000, // Traumatología
        ];

        return $amounts[$specialtyId] ?? 12000;
    }

    private function getRandomNotes()
    {
        $notes = [
            'Control de rutina',
            'Consulta por dolor',
            'Seguimiento de tratamiento',
            'Primera consulta',
            'Renovación de receta',
            'Chequeo anual',
            'Consulta por síntomas',
            null, null, null // Para que muchas citas no tengan notas
        ];

        return $notes[rand(0, count($notes) - 1)];
    }
}