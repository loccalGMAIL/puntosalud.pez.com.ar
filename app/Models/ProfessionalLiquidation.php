<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalLiquidation extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return 'Liquidación #' . $this->id;
    }

    protected $fillable = [
        'professional_id',
        'liquidation_date',
        'sheet_type',
        'appointments_total',
        'appointments_attended',
        'appointments_absent',
        'total_collected',
        'direct_payments_total',
        'professional_commission',
        'clinic_amount',
        'clinic_amount_from_direct',
        'net_professional_amount',
        'payment_status',
        'payment_method',
        'paid_at',
        'paid_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'liquidation_date' => 'date',
            'appointments_total' => 'integer',
            'appointments_attended' => 'integer',
            'appointments_absent' => 'integer',
            'total_collected' => 'decimal:2',
            'direct_payments_total' => 'decimal:2',
            'professional_commission' => 'decimal:2',
            'clinic_amount' => 'decimal:2',
            'clinic_amount_from_direct' => 'decimal:2',
            'net_professional_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function details()
    {
        return $this->hasMany(LiquidationDetail::class, 'liquidation_id');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('sheet_type', $type);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('liquidation_date', $date);
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }
}
