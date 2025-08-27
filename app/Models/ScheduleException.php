<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    use HasFactory;

    protected $fillable = [
        'exception_date',
        'reason',
        'affects_all',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exception_date' => 'date',
            'affects_all' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('exception_date', $date);
    }

    public function scopeAffectsAll($query)
    {
        return $query->where('affects_all', true);
    }
}