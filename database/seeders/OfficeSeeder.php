<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['name' => 'Consultorio 1', 'is_active' => true],
            ['name' => 'Consultorio 2', 'is_active' => true],
            ['name' => 'Consultorio 3', 'is_active' => true],
            ['name' => 'Sala A', 'is_active' => true],
            ['name' => 'Sala B', 'is_active' => false], // Para testing
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }
    }
}