<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileModule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'profile_id',
        'module',
    ];

    /**
     * Relaciones
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
