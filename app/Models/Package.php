<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sessions_included',
        'price',
        'is_active',
    ];

    protected $casts = [
        'sessions_included' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Un paquete tiene muchos paquetes de pacientes
     */
    public function patientPackages(): HasMany
    {
        return $this->hasMany(PatientPackage::class);
    }

    /**
     * Scope: Solo paquetes activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Solo paquetes inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Ordenar por precio
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    /**
     * Scope: Ordenar por sesiones
     */
    public function scopeOrderBySessions($query, string $direction = 'asc')
    {
        return $query->orderBy('sessions_included', $direction);
    }

    /**
     * Accessor: Precio por sesión
     */
    public function getPricePerSessionAttribute(): float
    {
        if ($this->sessions_included <= 0) {
            return 0;
        }

        return round($this->price / $this->sessions_included, 2);
    }

    /**
     * Accessor: Estado formateado
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-secondary">Inactivo</span>';
    }

    /**
     * Activar paquete
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Desactivar paquete
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }
}
