<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    public function activityDescription(): string
    {
        return $this->name . ' (' . $this->email . ')';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relaciones
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Último login registrado en ActivityLog
     */
    public function lastLogin()
    {
        return $this->hasOne(ActivityLog::class)->ofMany(
            ['created_at' => 'max'],
            fn ($query) => $query->where('action', 'login')
        );
    }

    /**
     * Verificar si el usuario tiene acceso a un módulo
     */
    public function canAccessModule(string $module): bool
    {
        if (! $this->profile) {
            return false;
        }

        return $this->profile->allowsModule($module);
    }

    /**
     * Alias semántico: administrador = acceso a configuración
     */
    public function isAdmin(): bool
    {
        return $this->canAccessModule('configuration');
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope para usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
