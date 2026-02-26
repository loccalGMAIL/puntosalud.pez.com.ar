<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PatientPackage extends Model
{
    use LogsActivity;

    public function activityDescription(): string
    {
        $parts = ['Paquete #' . $this->id];

        $patient = $this->patient()->first();
        if ($patient) {
            $parts[] = $patient->last_name . ', ' . $patient->first_name;
        }

        $package = $this->package()->first();
        if ($package) {
            $parts[] = '(' . $package->name . ')';
        }

        return implode(' - ', array_slice($parts, 0, 2)) . (isset($parts[2]) ? ' ' . $parts[2] : '');
    }
    protected $fillable = [
        'patient_id',
        'package_id',
        'payment_id',
        'sessions_included',
        'sessions_used',
        'price_paid',
        'purchase_date',
        'expires_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'sessions_included' => 'integer',
        'sessions_used' => 'integer',
        'price_paid' => 'decimal:2',
        'purchase_date' => 'date',
        'expires_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Un paquete de paciente pertenece a un paciente
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relación: Un paquete de paciente pertenece a un paquete del catálogo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Relación: Un paquete de paciente pertenece a un pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Scope: Solo paquetes activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Solo paquetes completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Solo paquetes expirados
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope: Solo paquetes cancelados
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: Paquetes con sesiones disponibles
     */
    public function scopeWithAvailableSessions($query)
    {
        return $query->whereRaw('sessions_used < sessions_included')
                     ->where('status', 'active');
    }

    /**
     * Scope: Paquetes por vencer (próximos N días)
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
                     ->whereNotNull('expires_at')
                     ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope: Por paciente
     */
    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Accessor: Sesiones restantes
     */
    public function getSessionsRemainingAttribute(): int
    {
        return max(0, $this->sessions_included - $this->sessions_used);
    }

    /**
     * Accessor: Porcentaje usado
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->sessions_included <= 0) {
            return 0;
        }

        return round(($this->sessions_used / $this->sessions_included) * 100, 2);
    }

    /**
     * Accessor: Está vencido
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Accessor: Días hasta vencimiento
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Accessor: Precio por sesión pagado
     */
    public function getPricePerSessionAttribute(): float
    {
        if ($this->sessions_included <= 0) {
            return 0;
        }

        return round($this->price_paid / $this->sessions_included, 2);
    }

    /**
     * Usar una sesión del paquete
     */
    public function useSession(): bool
    {
        if ($this->sessions_used >= $this->sessions_included) {
            return false;
        }

        $this->sessions_used++;

        // Si se usaron todas las sesiones, marcar como completado
        if ($this->sessions_used >= $this->sessions_included) {
            $this->status = 'completed';
        }

        return $this->save();
    }

    /**
     * Devolver una sesión (revertir uso)
     */
    public function returnSession(): bool
    {
        if ($this->sessions_used <= 0) {
            return false;
        }

        $this->sessions_used--;

        // Si se devuelve una sesión de un paquete completado, reactivarlo
        if ($this->status === 'completed') {
            $this->status = 'active';
        }

        return $this->save();
    }

    /**
     * Cancelar paquete
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Marcar como expirado
     */
    public function markAsExpired(): bool
    {
        $this->status = 'expired';
        return $this->save();
    }

    /**
     * Verificar y actualizar estado si está vencido
     */
    public function checkExpiration(): bool
    {
        if ($this->is_expired && $this->status === 'active') {
            return $this->markAsExpired();
        }

        return false;
    }
}
