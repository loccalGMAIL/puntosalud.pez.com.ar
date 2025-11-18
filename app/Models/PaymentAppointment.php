<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'appointment_id',
        'professional_id',
        'allocated_amount',
        'is_liquidation_trigger',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'is_liquidation_trigger' => 'boolean',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function liquidationDetails()
    {
        return $this->hasMany(LiquidationDetail::class);
    }

    public function scopeLiquidationTriggers($query)
    {
        return $query->where('is_liquidation_trigger', true);
    }
}
