<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_id',
        'day_of_week',
        'start_time',
        'end_time',
        'office_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function getDayNameAttribute()
    {
        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        return $days[$this->day_of_week] ?? '';
    }
}
