<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'liquidation_id',
        'payment_appointment_id',
        'payment_id',
        'appointment_id',
        'amount',
        'commission_amount',
        'concept',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function liquidation()
    {
        return $this->belongsTo(ProfessionalLiquidation::class, 'liquidation_id');
    }

    public function paymentAppointment()
    {
        return $this->belongsTo(PaymentAppointment::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}