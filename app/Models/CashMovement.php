<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_date',
        'type',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
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

    public function isIncome()
    {
        return $this->amount > 0;
    }

    public function isExpense()
    {
        return $this->amount < 0;
    }

    public function scopeOpeningMovements($query)
    {
        return $query->where('type', 'cash_opening');
    }

    public function scopeClosingMovements($query)
    {
        return $query->where('type', 'cash_closing');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('movement_date', $date);
    }

    public function isOpening()
    {
        return $this->type === 'cash_opening';
    }

    public function isClosing()
    {
        return $this->type === 'cash_closing';
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
        // Buscar días con apertura pero sin cierre
        $unclosedDates = static::openingMovements()
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('cash_movements as cm2')
                    ->whereRaw('DATE(cm2.movement_date) = DATE(cash_movements.movement_date)')
                    ->where('cm2.type', 'cash_closing');
            })
            ->where('movement_date', '<', now()->startOfDay())
            ->orderBy('movement_date', 'desc')
            ->first();

        return $unclosedDates ? $unclosedDates->movement_date->format('Y-m-d') : null;
    }
}
