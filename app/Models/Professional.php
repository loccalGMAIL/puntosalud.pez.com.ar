<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Professional extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return $this->last_name.', '.$this->first_name;
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'specialty_id',
        'default_office_id',
        'dni',
        'license_number',
        'phone',
        'email',
        'birthday',
        'commission_percentage',
        'receives_transfers_directly',
        'collects_directly',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'birthday' => 'date:Y-m-d',
        'commission_percentage' => 'decimal:2',
        'receives_transfers_directly' => 'boolean',
        'collects_directly' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relaciones
     *
     * @return BelongsTo<Specialty, $this>
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /** @return BelongsTo<Office, $this> */
    public function defaultOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'default_office_id');
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** @return HasMany<ProfessionalSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    /** @return HasOne<AppointmentSetting, $this> */
    public function appointmentSettings(): HasOne
    {
        return $this->hasOne(AppointmentSetting::class);
    }

    /** @return HasMany<ProfessionalLiquidation, $this> */
    public function liquidations(): HasMany
    {
        return $this->hasMany(ProfessionalLiquidation::class);
    }

    /** @return HasMany<ProfessionalNote, $this> */
    public function internalNotes(): HasMany
    {
        return $this->hasMany(ProfessionalNote::class)->latest();
    }

    /** @return HasMany<ProfessionalAbsence, $this> */
    public function absences(): HasMany
    {
        return $this->hasMany(ProfessionalAbsence::class);
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
