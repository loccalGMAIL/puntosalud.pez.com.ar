<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name' => 'Clínica Médica', 'description' => 'Medicina general'],
            ['name' => 'Cardiología', 'description' => 'Especialidad del corazón'],
            ['name' => 'Dermatología', 'description' => 'Especialidad de la piel'],
            ['name' => 'Traumatología', 'description' => 'Especialidad de huesos y articulaciones'],
        ];

        foreach ($specialties as $specialty) {
            Specialty::create($specialty);
        }
    }
}