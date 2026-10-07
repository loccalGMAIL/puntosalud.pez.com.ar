<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentSetting extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return 'Config turno #' . $this->id;
    }

    protected $fillable = [
        'professional_id',
        'default_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'default_duration_minutes' => 'integer',
        ];
    }

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
