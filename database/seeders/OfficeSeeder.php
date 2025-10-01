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
            ['name' => 'Consultorio 4', 'is_active' => true],
            ['name' => 'Consultorio 5', 'is_active' => true],
            ['name' => 'Consultorio 6', 'is_active' => true],
            ['name' => 'Consultorio 7', 'is_active' => true],
            ['name' => 'Consultorio 8', 'is_active' => true],
            ['name' => 'Consultorio 9', 'is_active' => true],
            ['name' => 'Consultorio 10', 'is_active' => true],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }
    }
}
