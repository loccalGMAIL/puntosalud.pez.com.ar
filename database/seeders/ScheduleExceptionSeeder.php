<?php

namespace Database\Seeders;

use App\Models\ScheduleException;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleExceptionSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();

        $exceptions = [
            [
                'exception_date' => '2025-01-01',
                'reason' => 'Año Nuevo',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-03-24',
                'reason' => 'Día Nacional de la Memoria por la Verdad y la Justicia',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-04-02',
                'reason' => 'Día del Veterano y de los Caídos en la Guerra de Malvinas',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-05-01',
                'reason' => 'Día del Trabajador',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-05-25',
                'reason' => 'Día de la Revolución de Mayo',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-06-20',
                'reason' => 'Paso a la Inmortalidad del General Manuel Belgrano',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-07-09',
                'reason' => 'Día de la Independencia',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-08-17',
                'reason' => 'Paso a la Inmortalidad del General José de San Martín',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-10-12',
                'reason' => 'Día del Respeto a la Diversidad Cultural',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-11-20',
                'reason' => 'Día de la Soberanía Nacional',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-12-08',
                'reason' => 'Día de la Inmaculada Concepción de María',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-12-25',
                'reason' => 'Navidad',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            // Algunos días específicos de ejemplo
            [
                'exception_date' => '2025-03-15',
                'reason' => 'Capacitación médica obligatoria',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'exception_date' => '2025-06-10',
                'reason' => 'Mantenimiento de equipos',
                'affects_all' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($exceptions as $exception) {
            ScheduleException::create($exception);
        }
    }
}
