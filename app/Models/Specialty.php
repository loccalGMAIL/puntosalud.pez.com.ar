<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return $this->name;
    }

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relaciones
     */
    public function professionals()
    {
        return $this->hasMany(Professional::class);
    }

    /**
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
