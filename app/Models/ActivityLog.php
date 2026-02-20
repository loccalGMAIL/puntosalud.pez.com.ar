<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    // Log inmutable: sin updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'subject_description',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper central para registrar actividad.
     *
     * @param  string       $action       created|updated|deleted|login|logout
     * @param  \Illuminate\Database\Eloquent\Model|null  $subject
     * @param  string|null  $description  Texto legible del sujeto
     * @param  int|null     $userId       Si es null, usa el usuario autenticado actual
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?int $userId = null
    ): void {
        try {
            $userId ??= Auth::id();

            static::create([
                'user_id'             => $userId,
                'action'              => $action,
                'subject_type'        => $subject ? class_basename($subject) : null,
                'subject_id'          => $subject?->getKey(),
                'subject_description' => $description,
                'ip_address'          => Request::ip(),
            ]);
        } catch (\Throwable) {
            // No interrumpir el flujo principal si el log falla
        }
    }

    /**
     * Scope para filtros de búsqueda.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        return $query;
    }
}
