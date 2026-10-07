<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'liquidation_id',
        'payment_detail_id',
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

    /** @return BelongsTo<ProfessionalLiquidation, $this> */
    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(ProfessionalLiquidation::class, 'liquidation_id');
    }

    /** @return BelongsTo<PaymentDetail, $this> */
    public function paymentDetail(): BelongsTo
    {
        return $this->belongsTo(PaymentDetail::class);
    }

    /** @return BelongsTo<PaymentAppointment, $this> */
    public function paymentAppointment(): BelongsTo
    {
        return $this->belongsTo(PaymentAppointment::class);
    }

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
