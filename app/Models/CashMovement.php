<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_date',
        'movement_type_id',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'balance_after',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    // Relaciones

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movementType()
    {
        return $this->belongsTo(MovementType::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes

    /**
     * Filtrar por código de tipo de movimiento
     * @param string $typeCode Código del tipo (ej: 'patient_payment', 'expense', etc.)
     */
    public function scopeByType($query, $typeCode)
    {
        return $query->whereHas('movementType', function ($q) use ($typeCode) {
            $q->where('code', $typeCode);
        });
    }

    /**
     * Filtrar por ID de tipo de movimiento
     */
    public function scopeByTypeId($query, $typeId)
    {
        return $query->where('movement_type_id', $typeId);
    }

    public function scopeIncome($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeExpense($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function scopeOpeningMovements($query)
    {
        return $query->whereHas('movementType', function ($q) {
            $q->where('code', 'cash_opening');
        });
    }

    public function scopeClosingMovements($query)
    {
        return $query->whereHas('movementType', function ($q) {
            $q->where('code', 'cash_closing');
        });
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('movement_date', $date);
    }

    // Métodos helper

    public function isIncome()
    {
        return $this->amount > 0;
    }

    public function isExpense()
    {
        return $this->amount < 0;
    }

    public function isOpening()
    {
        return $this->movementType?->code === 'cash_opening';
    }

    public function isClosing()
    {
        return $this->movementType?->code === 'cash_closing';
    }

    public static function getCashStatusForDate($date)
    {
        $opening = static::forDate($date)->openingMovements()->first();
        $closing = static::forDate($date)->closingMovements()->first();

        return [
            'is_open' => $opening && ! $closing,
            'is_closed' => $opening && $closing,
            'needs_opening' => ! $opening,
            'opening_movement' => $opening,
            'closing_movement' => $closing,
        ];
    }

    public static function hasUnclosedCash()
    {
        // Obtener el ID del tipo 'cash_closing'
        $closingTypeId = MovementType::where('code', 'cash_closing')->value('id');

        // Buscar días con apertura pero sin cierre
        $unclosedDates = static::openingMovements()
            ->whereNotExists(function ($query) use ($closingTypeId) {
                $query->select('id')
                    ->from('cash_movements as cm2')
                    ->whereRaw('DATE(cm2.movement_date) = DATE(cash_movements.movement_date)')
                    ->where('cm2.movement_type_id', $closingTypeId);
            })
            ->where('movement_date', '<', now()->startOfDay())
            ->orderBy('movement_date', 'desc')
            ->first();

        return $unclosedDates ? $unclosedDates->movement_date->format('Y-m-d') : null;
    }

    /**
     * Obtiene el balance actual de caja con lock pesimista para evitar condiciones de carrera
     *
     * @return float El balance actual de caja
     */
    public static function getCurrentBalanceWithLock()
    {
        $lastMovement = static::orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->lockForUpdate()
            ->first();

        return $lastMovement ? $lastMovement->balance_after : 0;
    }
}
