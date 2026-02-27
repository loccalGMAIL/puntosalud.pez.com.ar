<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\ProfileModule;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $allModules = array_keys(Profile::MODULES);

        $generalModules = [
            'professionals',
            'patients',
            'appointments',
            'agenda',
            'cash',
            'payments',
            'reports',
        ];

        // Perfil Administrador — acceso completo
        $admin = Profile::firstOrCreate(
            ['name' => 'Administrador'],
            ['description' => 'Acceso completo a todos los módulos del sistema']
        );
        $admin->modules()->delete();
        foreach ($allModules as $module) {
            ProfileModule::create(['profile_id' => $admin->id, 'module' => $module]);
        }

        // Perfil Acceso General — sin configuración ni sistema
        $general = Profile::firstOrCreate(
            ['name' => 'Acceso General'],
            ['description' => 'Acceso a módulos operativos sin configuración ni sistema']
        );
        $general->modules()->delete();
        foreach ($generalModules as $module) {
            ProfileModule::create(['profile_id' => $general->id, 'module' => $module]);
        }
    }
}
