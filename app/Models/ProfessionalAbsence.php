<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ProfessionalAbsence extends Model
{
    use LogsActivity;

    protected $fillable = [
        'professional_id',
        'absence_date',
        'reason',
    ];

    protected $casts = [
        'absence_date' => 'date',
    ];

    public function activityDescription(): string
    {
        $professional = $this->professional ?? $this->professional()->first();

        if ($professional) {
            return 'Ausencia — ' . $professional->last_name . ', ' . $professional->first_name . ' (' . $this->absence_date->format('d/m/Y') . ')';
        }

        return 'Ausencia #' . $this->getKey();
    }

    /**
     * Relaciones
     */
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * Scopes
     */
    public function scopeForProfessional($query, $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('absence_date', [$start, $end]);
    }

    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('absence_date', $year)->whereMonth('absence_date', $month);
    }
}
