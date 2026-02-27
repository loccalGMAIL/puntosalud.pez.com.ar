<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminProfile = Profile::where('name', 'Administrador')->first();
        $generalProfile = Profile::where('name', 'Acceso General')->first();

        // Usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@puntosalud.com',
            'password' => Hash::make('password123'),
            'profile_id' => $adminProfile?->id,
            'is_active' => true,
        ]);

        // Usuario recepcionista
        User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcion@puntosalud.com',
            'password' => Hash::make('password123'),
            'profile_id' => $generalProfile?->id,
            'is_active' => true,
        ]);

        // Usuario Priscila
        User::create([
            'name' => 'Priscila',
            'email' => 'gomezpri20@gmail.com',
            'password' => Hash::make('password123'),
            'profile_id' => $generalProfile?->id,
            'is_active' => true,
        ]);
    }
}
