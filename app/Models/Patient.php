<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'dni',
        'phone',
        'email',
        'health_insurance',
        'health_insurance_number',
        'titular_obra_social',
        'plan_obra_social',
        'birth_date',
        'address',
        'activo',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'activo' => 'boolean',
    ];

    protected $appends = [
        'is_active',
        'full_name',
    ];

    /**
     * Relaciones
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('dni', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeWithHealthInsurance($query, $insurance)
    {
        return $query->where('health_insurance', $insurance);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('last_name')->orderBy('first_name');
    }

    /**
     * Accessors & Mutators
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getIsActiveAttribute()
    {
        return $this->activo;
    }

    /**
     * Helpers
     */
    public function getLastAppointment()
    {
        return $this->appointments()
            ->orderBy('appointment_date', 'desc')
            ->first();
    }

    public function getUpcomingAppointments()
    {
        return $this->appointments()
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('appointment_date')
            ->get();
    }

    public function getTotalDebt()
    {
        // Turnos atendidos sin pago asociado
        $attendedAppointments = $this->appointments()
            ->where('status', 'attended')
            ->whereDoesntHave('paymentAppointments')
            ->sum('final_amount');

        return $attendedAppointments ?? 0;
    }

    public function getActivePackages()
    {
        return $this->payments()
            ->where('payment_type', 'package')
            ->whereColumn('sessions_used', '<', 'sessions_included')
            ->get();
    }
}
