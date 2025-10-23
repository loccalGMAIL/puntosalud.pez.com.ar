<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'specialty_id',
        'dni',
        'license_number',
        'phone',
        'email',
        'commission_percentage',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    public function appointmentSettings()
    {
        return $this->hasOne(AppointmentSetting::class);
    }

    public function liquidations()
    {
        return $this->hasMany(ProfessionalLiquidation::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithSpecialty($query, $specialtyId)
    {
        return $query->where('specialty_id', $specialtyId);
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

    public function getFormattedCommissionAttribute()
    {
        return "{$this->commission_percentage}%";
    }

    /**
     * Helpers
     */
    public function calculateCommission($amount)
    {
        return $amount * ($this->commission_percentage / 100);
    }

    public function getClinicAmount($amount)
    {
        return $amount - $this->calculateCommission($amount);
    }

    public function getScheduleForDay($dayOfWeek)
    {
        return $this->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();
    }

    public function hasAppointmentAt($dateTime)
    {
        return $this->appointments()
            ->where('appointment_date', $dateTime)
            ->whereNotIn('status', ['cancelled'])
            ->exists();
    }
}
