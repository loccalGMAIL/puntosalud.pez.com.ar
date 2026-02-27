<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla de perfiles
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Crear tabla pivot profile_modules
        Schema::create('profile_modules', function (Blueprint $table) {
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->string('module');
            $table->unique(['profile_id', 'module']);
        });

        // 3. Agregar profile_id a users (nullable para no romper usuarios existentes)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('id')->constrained('profiles')->nullOnDelete();
        });

        // 4. Crear perfiles base y asignar usuarios existentes
        $this->seedProfiles();

        // 5. Eliminar columna role
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        // Restaurar columna role antes de eliminar profile_id
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'receptionist'])->default('receptionist')->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_id');
        });

        Schema::dropIfExists('profile_modules');
        Schema::dropIfExists('profiles');
    }

    private function seedProfiles(): void
    {
        $allModules = [
            'professionals',
            'patients',
            'appointments',
            'agenda',
            'cash',
            'payments',
            'reports',
            'configuration',
            'system',
        ];

        $generalModules = [
            'professionals',
            'patients',
            'appointments',
            'agenda',
            'cash',
            'payments',
            'reports',
        ];

        // Crear perfil Administrador
        $adminProfileId = DB::table('profiles')->insertGetId([
            'name' => 'Administrador',
            'description' => 'Acceso completo a todos los módulos del sistema',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($allModules as $module) {
            DB::table('profile_modules')->insert([
                'profile_id' => $adminProfileId,
                'module' => $module,
            ]);
        }

        // Crear perfil Acceso General
        $generalProfileId = DB::table('profiles')->insertGetId([
            'name' => 'Acceso General',
            'description' => 'Acceso a módulos operativos sin configuración ni sistema',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($generalModules as $module) {
            DB::table('profile_modules')->insert([
                'profile_id' => $generalProfileId,
                'module' => $module,
            ]);
        }

        // Asignar perfiles a usuarios existentes según su rol actual
        DB::table('users')->where('role', 'admin')->update(['profile_id' => $adminProfileId]);
        DB::table('users')->where('role', 'receptionist')->update(['profile_id' => $generalProfileId]);
    }
};
