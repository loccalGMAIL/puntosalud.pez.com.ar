<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return $this->name;
    }

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function professionalSchedules()
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helpers
     */
    public function isAvailableAt($dateTime)
    {
        return ! $this->appointments()
            ->where('appointment_date', '<=', $dateTime)
            ->where(function ($query) use ($dateTime) {
                $query->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?', [$dateTime]);
            })
            ->whereNotIn('status', ['cancelled'])
            ->exists();
    }

    public function getScheduleForDate($date)
    {
        $dayOfWeek = date('N', strtotime($date)); // 1=Lunes, 7=Domingo

        return $this->professionalSchedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->with('professional')
            ->get();
    }
}
