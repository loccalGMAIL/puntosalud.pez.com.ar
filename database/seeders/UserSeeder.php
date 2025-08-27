<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@puntosalud.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Usuario recepcionista
        User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcion@puntosalud.com',
            'password' => Hash::make('password123'),
            'role' => 'receptionist',
            'is_active' => true,
        ]);
    }
}