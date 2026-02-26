<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        $base = $this->receipt_number ? 'Recibo #' . $this->receipt_number : 'Pago #' . $this->id;

        if ($this->patient_id) {
            $patient = $this->patient()->first();
            $patientName = $patient ? $patient->last_name . ', ' . $patient->first_name : '';
            return $patientName ? $base . ' - ' . $patientName : $base;
        }

        if ($this->concept) {
            return $base . ' - ' . \Illuminate\Support\Str::limit($this->concept, 40);
        }

        return $base;
    }

    protected $fillable = [
        'patient_id',
        'payment_date',
        'payment_type',
        'total_amount',
        'is_advance_payment',
        'concept',
        'status',
        'liquidation_status',
        'liquidated_at',
        'income_category', // Para ingresos manuales (código del MovementType)
        'receipt_number',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'liquidated_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'is_advance_payment' => 'boolean',
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

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function patientPackage()
    {
        return $this->hasOne(PatientPackage::class);
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
        return $query->where('payment_type', 'package_purchase');
    }

    public function scopeSinglePayments($query)
    {
        return $query->where('payment_type', 'single');
    }

    public function scopeRefunds($query)
    {
        return $query->where('payment_type', 'refund');
    }

    public function scopeManualIncome($query)
    {
        return $query->where('payment_type', 'manual_income');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('payment_date', [$start, $end]);
    }

    public function scopeAdvancePayments($query)
    {
        return $query->where('is_advance_payment', true);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Accessors
     */

    /**
     * Determina si el pago es de un paciente o un ingreso manual
     */
    public function getEntryTypeAttribute()
    {
        return $this->payment_type === 'manual_income' ? 'income' : 'payment';
    }

    /**
     * Obtiene el método de pago principal del primer payment_detail
     * Para compatibilidad con vistas que esperan payment_method directo
     */
    public function getPaymentMethodAttribute()
    {
        // Si ya está cargada la relación, usarla
        if ($this->relationLoaded('paymentDetails')) {
            $firstDetail = $this->paymentDetails->first();
            return $firstDetail ? $firstDetail->payment_method : null;
        }

        // Sino, hacer query
        $firstDetail = $this->paymentDetails()->first();
        return $firstDetail ? $firstDetail->payment_method : null;
    }

    /**
     * Alias para total_amount (compatibilidad con código legacy)
     */
    public function getAmountAttribute()
    {
        return $this->total_amount;
    }

    public function getIsRefundAttribute()
    {
        return $this->total_amount < 0;
    }

    public function getIsManualIncomeAttribute()
    {
        return $this->payment_type === 'manual_income';
    }

    public function getIsPackagePurchaseAttribute()
    {
        return $this->payment_type === 'package_purchase';
    }

    public function getIsAdvanceAttribute()
    {
        return $this->is_advance_payment;
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsConfirmedAttribute()
    {
        return $this->status === 'confirmed';
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

    public function canBeUsedForAppointment()
    {
        // Si es compra de paquete, verificar que el paquete tenga sesiones disponibles
        if ($this->payment_type === 'package_purchase') {
            return $this->patientPackage &&
                   $this->patientPackage->sessions_remaining > 0 &&
                   $this->patientPackage->status === 'active';
        }

        // Si es pago individual, solo puede usarse una vez
        if ($this->payment_type === 'single') {
            return $this->paymentAppointments()->count() === 0;
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

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    public function getTotalReceivedByCentro()
    {
        return $this->paymentDetails()
            ->where('received_by', 'centro')
            ->sum('amount');
    }

    public function getTotalReceivedByProfesional()
    {
        return $this->paymentDetails()
            ->where('received_by', 'profesional')
            ->sum('amount');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // No generar receipt_number para gastos (expenses)
            if ($payment->payment_type === 'expense') {
                return;
            }

            if (empty($payment->receipt_number)) {
                $payment->receipt_number = self::generateReceiptNumber();
            }
        });
    }

    /**
     * Generate receipt number
     *
     * Formato: YYYYMMNNNN (10 dígitos)
     * - YYYY: Año (4 dígitos)
     * - MM: Mes (2 dígitos)
     * - NNNN: Número secuencial del mes (4 dígitos, desde 0001 hasta 9999)
     *
     * Ejemplo: 2025100149 = Año 2025, Mes 10 (Octubre), Recibo #149 del mes
     *
     * La secuencia se reinicia cada mes.
     */
    public static function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastPayment = self::whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastPayment && $lastPayment->receipt_number) {
            $lastNumber = intval(substr($lastPayment->receipt_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
