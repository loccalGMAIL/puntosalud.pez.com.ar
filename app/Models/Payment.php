<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'payment_date',
        'payment_type',
        'payment_method',
        'amount',
        'sessions_included',
        'sessions_used',
        'liquidation_status',
        'liquidated_at',
        'concept',
        'receipt_number',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'liquidated_at' => 'datetime',
        'amount' => 'decimal:2',
        'sessions_included' => 'integer',
        'sessions_used' => 'integer',
    ];

    /**
     * Relaciones
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentAppointments()
    {
        return $this->hasMany(PaymentAppointment::class);
    }

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'payment_appointments')
            ->withPivot('allocated_amount', 'is_liquidation_trigger')
            ->withTimestamps();
    }

    public function liquidationDetails()
    {
        return $this->hasMany(LiquidationDetail::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('liquidation_status', 'pending');
    }

    public function scopeLiquidated($query)
    {
        return $query->where('liquidation_status', 'liquidated');
    }

    public function scopePackages($query)
    {
        return $query->where('payment_type', 'package');
    }

    public function scopeSinglePayments($query)
    {
        return $query->where('payment_type', 'single');
    }

    public function scopeRefunds($query)
    {
        return $query->where('payment_type', 'refund');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('payment_date', [$start, $end]);
    }

    /**
     * Accessors
     */
    public function getSessionsRemainingAttribute()
    {
        if ($this->payment_type !== 'package') {
            return 0;
        }

        return $this->sessions_included - $this->sessions_used;
    }

    public function getIsPackageCompleteAttribute()
    {
        return $this->payment_type === 'package' &&
               $this->sessions_used >= $this->sessions_included;
    }

    public function getIsRefundAttribute()
    {
        return $this->amount < 0;
    }

    /**
     * Helpers
     */
    public function allocateToAppointment($appointmentId, $amount, $isLiquidationTrigger = false)
    {
        return PaymentAppointment::create([
            'payment_id' => $this->id,
            'appointment_id' => $appointmentId,
            'allocated_amount' => $amount,
            'is_liquidation_trigger' => $isLiquidationTrigger,
        ]);
    }

    public function useSession()
    {
        if ($this->payment_type === 'package' && $this->sessions_remaining > 0) {
            $this->increment('sessions_used');

            return true;
        }

        return false;
    }

    public function canBeUsedForAppointment()
    {
        if ($this->payment_type === 'single') {
            return $this->paymentAppointments()->count() === 0;
        }

        if ($this->payment_type === 'package') {
            return $this->sessions_remaining > 0;
        }

        return false;
    }

    public function markAsLiquidated()
    {
        $this->update([
            'liquidation_status' => 'liquidated',
            'liquidated_at' => now(),
        ]);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = self::generateReceiptNumber();
            }
        });
    }

    /**
     * Generate receipt number
     */
    public static function generateReceiptNumber()
    {
        $year = date('Y');
        $lastPayment = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastPayment ? (intval(substr($lastPayment->receipt_number, -6)) + 1) : 1;

        return sprintf('REC-%s-%06d', $year, $number);
    }
}
