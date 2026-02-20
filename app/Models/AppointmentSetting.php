<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
