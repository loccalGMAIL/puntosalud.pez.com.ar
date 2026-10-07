<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleException extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return 'Excepción ' . ($this->exception_date?->format('Y-m-d') ?? '#' . $this->id);
    }

    protected $fillable = [
        'exception_date',
        'end_date',
        'reason',
        'type',
        'affects_all',
        'professional_id',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exception_date' => 'date',
            'end_date' => 'date',
            'affects_all' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    // Scopes
    public function scopeByDate($query, $date)
    {
        return $query->where('exception_date', $date);
    }

    public function scopeAffectsAll($query)
    {
        return $query->where('affects_all', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHolidays($query)
    {
        return $query->where('type', 'holiday');
    }

    public function scopeVacations($query)
    {
        return $query->where('type', 'vacation');
    }

    public function scopeCustom($query)
    {
        return $query->where('type', 'custom');
    }

    public function scopeForProfessional($query, $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }
}
