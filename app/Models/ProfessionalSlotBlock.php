<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalSlotBlock extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'professional_id',
        'block_date',
        'start_time',
        'end_time',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'block_date' => 'date',
    ];

    public function activityDescription(): string
    {
        $professional = $this->professional ?? $this->professional()->first();
        $rango = substr((string) $this->start_time, 0, 5).'–'.substr((string) $this->end_time, 0, 5);

        if ($professional) {
            return 'Bloqueo de horario '.$rango.' — '.$professional->last_name.', '.$professional->first_name.' ('.$this->block_date->format('d/m/Y').')';
        }

        return 'Bloqueo de horario #'.$this->getKey();
    }

    /**
     * Relaciones
     *
     * @return BelongsTo<Professional, $this>
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeForProfessional($query, $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('block_date', $date);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('block_date', [$start, $end]);
    }

    /**
     * Bloqueos que se superponen con el rango HH:MM[:SS] indicado.
     */
    public function scopeOverlapping($query, string $start, string $end)
    {
        return $query->where('start_time', '<', $end)->where('end_time', '>', $start);
    }
}
