<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    /**
     * Módulos disponibles en el sistema
     */
    public const MODULES = [
        'professionals' => 'Profesionales',
        'patients'      => 'Pacientes',
        'appointments'  => 'Turnos',
        'agenda'        => 'Agenda',
        'cash'          => 'Caja',
        'payments'      => 'Cobros',
        'reports'       => 'Reportes',
        'configuration' => 'Configuración',
        'system'        => 'Sistema',
    ];

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relaciones
     */
    public function modules(): HasMany
    {
        return $this->hasMany(ProfileModule::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verificar si el perfil permite acceso a un módulo
     */
    public function allowsModule(string $module): bool
    {
        return $this->modules->contains('module', $module);
    }
}
