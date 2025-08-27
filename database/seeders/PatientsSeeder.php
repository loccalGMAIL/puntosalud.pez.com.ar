<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientsSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            [
                'first_name' => 'María',
                'last_name' => 'González',
                'dni' => '25.678.910',
                'birth_date' => '1980-03-15',
                'email' => 'maria.gonzalez@gmail.com',
                'phone' => '+54 11 2345-6789',
                'address' => 'Av. Corrientes 1234, CABA',
                'health_insurance' => 'OSDE',
                'health_insurance_number' => '12345678',
                // Removed medical_notes field as it doesn't exist in the new schema
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'dni' => '18.456.789',
                'birth_date' => '1970-07-22',
                'email' => 'carlos.rodriguez@hotmail.com',
                'phone' => '+54 11 3456-7890',
                'address' => 'San Martín 567, Córdoba',
                'health_insurance' => 'Swiss Medical',
                'health_insurance_number' => '87654321',
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Martínez',
                'dni' => '30.123.456',
                'birth_date' => '1990-12-10',
                'email' => null,
                'phone' => '+54 351 444-5555',
                'address' => null,
                'health_insurance' => null,
                'health_insurance_number' => null,
            ],
            [
                'first_name' => 'Roberto',
                'last_name' => 'López',
                'dni' => '12.345.678',
                'birth_date' => '1955-05-30',
                'email' => 'roberto.lopez@yahoo.com',
                'phone' => '+54 351 555-6666',
                'address' => 'Belgrano 890, Córdoba',
                'health_insurance' => 'PAMI',
                'health_insurance_number' => '98765432',
            ],
            [
                'first_name' => 'Lucía',
                'last_name' => 'Fernández',
                'dni' => '35.987.654',
                'birth_date' => '1995-09-18',
                'email' => 'lucia.fernandez@gmail.com',
                'phone' => '+54 351 777-8888',
                'address' => 'Rivadavia 456, Córdoba',
                'health_insurance' => 'Medicus',
                'health_insurance_number' => '11223344',
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Sánchez',
                'dni' => '20.111.222',
                'birth_date' => '1975-11-03',
                'email' => 'pedro.sanchez@outlook.com',
                'phone' => '+54 351 888-9999',
                'address' => 'Colón 123, Córdoba',
                'health_insurance' => null,
                'health_insurance_number' => null,
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Torres',
                'dni' => '28.333.444',
                'birth_date' => '1985-02-14',
                'email' => 'elena.torres@gmail.com',
                'phone' => '+54 351 999-0000',
                'address' => 'Independencia 789, Córdoba',
                'health_insurance' => 'Galeno',
                'health_insurance_number' => '55667788',
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'Morales',
                'dni' => '22.555.666',
                'birth_date' => '1978-08-25',
                'email' => null,
                'phone' => '+54 351 111-2222',
                'address' => 'Vélez Sarsfield 321, Córdoba',
                'health_insurance' => 'OSDE',
                'health_insurance_number' => '99887766',
            ],
        ];

        foreach ($patients as $patient) {
            Patient::create($patient);
        }
    }
}