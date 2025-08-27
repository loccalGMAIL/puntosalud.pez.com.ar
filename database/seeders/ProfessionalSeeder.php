<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class ProfessionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos profesionales específicos
        $professionals = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Pérez García',
                'email' => 'juan.perez@clinic.com',
                'phone' => '+54 11 4567-8901',
                'dni' => '20.123.456',
                'specialty_id' => 1,
                'commission_percentage' => 15.00,
                'is_active' => true,
            ],
            [
                'first_name' => 'María',
                'last_name' => 'González López',
                'email' => 'maria.gonzalez@clinic.com',
                'phone' => '+54 11 4567-8902',
                'dni' => '25.234.567',
                'specialty_id' => 2,
                'commission_percentage' => 20.00,
                'is_active' => true,
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Martínez Silva',
                'email' => 'carlos.martinez@clinic.com',
                'phone' => '+54 11 4567-8903',
                'dni' => '18.345.678',
                'specialty_id' => 3,
                'commission_percentage' => 18.50,
                'is_active' => true,
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Rodríguez Méndez',
                'email' => 'ana.rodriguez@clinic.com',
                'phone' => '+54 11 4567-8904',
                'dni' => '22.456.789',
                'specialty_id' => 1,
                'commission_percentage' => 22.00,
                'is_active' => true,
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Fernández Castro',
                'email' => 'luis.fernandez@clinic.com',
                'phone' => '+54 11 4567-8905',
                'dni' => '19.567.890',
                'specialty_id' => 4,
                'commission_percentage' => 17.50,
                'is_active' => false,
            ]
        ];

        foreach ($professionals as $professional) {
            Professional::create($professional);
        }
    }
}