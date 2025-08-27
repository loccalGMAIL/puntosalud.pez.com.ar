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
}