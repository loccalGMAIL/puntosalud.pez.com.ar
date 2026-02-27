<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        $base = 'Turno #' . $this->id . ' - ' . ($this->patient->full_name ?? 'Sin paciente');

        if ($this->wasChanged('status')) {
            $labels = [
                'scheduled' => 'Programado',
                'attended'  => 'Atendido',
                'absent'    => 'Ausente',
                'cancelled' => 'Cancelado',
            ];
            return $base . ' → ' . ($labels[$this->status] ?? $this->status);
        }

        return $base;
    }

    protected $fillable = [
        'professional_id',
        'patient_id',
        'appointment_date',
        'duration',
        'office_id',
        'status',
        'estimated_amount',
        'final_amount',
        'notes',
        'created_by',
        'is_between_turn',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'estimated_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'is_between_turn' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentAppointments()
    {
        return $this->hasMany(PaymentAppointment::class);
    }

    public function liquidationDetails()
    {
        return $this->hasMany(LiquidationDetail::class);
    }

    /**
     * Scopes
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['scheduled']);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['attended', 'absent']);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeForProfessional($query, $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'attended')
            ->whereDoesntHave('paymentAppointments');
    }

    public function scopeUrgency($query)
    {
        return $query->where('duration', 0);
    }

    public function scopeRegular($query)
    {
        return $query->where('duration', '>', 0);
    }

    /**
     * Accessors
     */
    public function getEndTimeAttribute()
    {
        return $this->appointment_date->copy()->addMinutes($this->duration);
    }

    public function getIsPaidAttribute()
    {
        return $this->paymentAppointments()->exists();
    }

    public function getAmountPaidAttribute()
    {
        return $this->paymentAppointments()->sum('allocated_amount');
    }

    public function getPendingAmountAttribute()
    {
        if ($this->status !== 'attended') {
            return 0;
        }

        $finalAmount = $this->final_amount ?? 0;
        $amountPaid = $this->amount_paid ?? 0;

        return $finalAmount - $amountPaid;
    }

    public function getIsUrgencyAttribute()
    {
        return $this->duration === 0;
    }

    /**
     * Helpers
     */
    public function markAsAttended($finalAmount = null)
    {
        $this->update([
            'status' => 'attended',
            'final_amount' => $finalAmount ?? $this->estimated_amount,
        ]);
    }

    public function markAsAbsent()
    {
        $this->update([
            'status' => 'absent',
            'final_amount' => 0,
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['scheduled']) &&
               $this->appointment_date->isFuture();
    }

    public function conflictsWith($startTime, $endTime)
    {
        $appointmentEnd = $this->end_time;

        return ! ($endTime <= $this->appointment_date || $startTime >= $appointmentEnd);
    }
}
