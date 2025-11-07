<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDetail extends Model
{
    protected $fillable = [
        'payment_id',
        'payment_method',
        'amount',
        'received_by',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Un detalle de pago pertenece a un pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Scope: Filtrar por método de pago
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: Filtrar por receptor (centro o profesional)
     */
    public function scopeReceivedBy($query, string $receivedBy)
    {
        return $query->where('received_by', $receivedBy);
    }

    /**
     * Scope: Solo efectivo
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    /**
     * Scope: Solo transferencias
     */
    public function scopeTransfer($query)
    {
        return $query->where('payment_method', 'transfer');
    }

    /**
     * Scope: Recibidos por el centro
     */
    public function scopeToCentro($query)
    {
        return $query->where('received_by', 'centro');
    }

    /**
     * Scope: Recibidos por profesional
     */
    public function scopeToProfesional($query)
    {
        return $query->where('received_by', 'profesional');
    }

    /**
     * Accessor: Nombre del método de pago
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Efectivo',
            'transfer' => 'Transferencia',
            'debit_card' => 'Tarjeta de Débito',
            'credit_card' => 'Tarjeta de Crédito',
            'other' => 'Otro',
            default => 'Desconocido',
        };
    }

    /**
     * Accessor: Nombre del receptor
     */
    public function getReceivedByNameAttribute(): string
    {
        return match($this->received_by) {
            'centro' => 'Centro',
            'profesional' => 'Profesional',
            default => 'Desconocido',
        };
    }
}
