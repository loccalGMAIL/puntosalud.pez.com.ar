<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'expense_date',
        'movement_type_id',
        'amount',
        'payment_method',
        'description',
        'notes',
        'receipt_path',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /** Relaciones */
    public function movementType()
    {
        return $this->belongsTo(MovementType::class, 'movement_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Scopes */
    public function scopeForDateRange($query, $from, $to)
    {
        return $query->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to);
    }

    public function scopeByType($query, $movementTypeId)
    {
        return $query->where('movement_type_id', $movementTypeId);
    }

    public function activityDescription(): string
    {
        $type = $this->movementType?->name;
        $desc = $this->description;

        return trim(($type ? $type . ' - ' : '') . ($desc ?: '#' . $this->getKey()));
    }
}
