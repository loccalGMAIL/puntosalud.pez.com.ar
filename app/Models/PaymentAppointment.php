<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    /** @return HasMany<LiquidationDetail, $this> */
    public function liquidationDetails(): HasMany
    {
        return $this->hasMany(LiquidationDetail::class);
    }

    public function scopeLiquidationTriggers($query)
    {
        return $query->where('is_liquidation_trigger', true);
    }
}
