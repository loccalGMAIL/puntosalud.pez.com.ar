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
        'whatsapp'      => 'WhatsApp',
        'configuration' => 'Configuración',
        'system'        => 'Sistema',
    ];

    /**
     * Sub-permisos granulares disponibles, agrupados por módulo padre
     */
    public const PERMISSIONS = [
        'reports' => [
            'reports.financiero.cash'          => 'Movimientos de Caja',
            'reports.financiero.expenses'      => 'Informe de Gastos',
            'reports.financiero.liquidaciones' => 'Liquidaciones Históricas',
            'reports.financiero.pagos'         => 'Métodos de Pago',
            'reports.financiero.os'            => 'Ingresos Obra Social',
            'reports.financiero.cobros'        => 'Cobros Pendientes',
            'reports.financiero.flujo'         => 'Flujo Mensual',
        ],
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

    public function permissions(): HasMany
    {
        return $this->hasMany(ProfilePermission::class);
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

    /**
     * Verificar si el perfil tiene un sub-permiso específico
     */
    public function allowsPermission(string $permission): bool
    {
        return $this->permissions->contains('permission', $permission);
    }
}
