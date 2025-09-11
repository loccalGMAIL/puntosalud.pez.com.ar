<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Orden importante: los seeders deben ejecutarse respetando las dependencias
        $this->call([
            // 1. Datos básicos sin dependencias
            UserSeeder::class,
            SpecialtySeeder::class,
            OfficeSeeder::class,
            
            // 2. Datos que dependen de especialidades
            ProfessionalSeeder::class,
            PatientsSeeder::class,
            
            // 3. Configuraciones que dependen de profesionales
            // ProfessionalScheduleSeeder::class,
            // AppointmentSettingSeeder::class,
            
            // // 4. Excepciones de horario
            // ScheduleExceptionSeeder::class,
            
            // // 5. Citas que dependen de profesionales, pacientes y horarios
            // AppointmentSeeder::class,
            
            // // 6. Pagos que dependen de pacientes
            // PaymentSeeder::class,
            
            // // 7. Movimientos de caja que dependen de pagos
            // CashMovementSeeder::class,
        ]);
    }
}