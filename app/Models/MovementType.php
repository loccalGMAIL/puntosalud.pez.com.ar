<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementType extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        return $this->name;
    }

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'affects_balance',
        'icon',
        'color',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'affects_balance' => 'integer',
            'order' => 'integer',
        ];
    }

    // Relaciones

    /**
     * Movimientos de caja que usan este tipo
     */
    public function cashMovements()
    {
        return $this->hasMany(CashMovement::class, 'movement_type_id');
    }

    // Scopes

    /**
     * Solo tipos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtrar por categoría
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Tipos que afectan positivamente el balance (ingresos)
     */
    public function scopeIncome($query)
    {
        return $query->where('affects_balance', 1);
    }

    /**
     * Tipos que afectan negativamente el balance (egresos)
     */
    public function scopeExpense($query)
    {
        return $query->where('affects_balance', -1);
    }

    // Métodos helper

    /**
     * Verifica si es un ingreso
     */
    public function isIncome(): bool
    {
        return $this->affects_balance === 1;
    }

    /**
     * Verifica si es un egreso
     */
    public function isExpense(): bool
    {
        return $this->affects_balance === -1;
    }

    /**
     * Obtiene el icono con fallback
     */
    public function getIconAttribute($value): string
    {
        return $value ?: '📋';
    }

    /**
     * Obtiene el color con fallback
     */
    public function getColorAttribute($value): string
    {
        return $value ?: 'gray';
    }

    /**
     * Obtiene el ID de un tipo de movimiento por su código
     * Con caché en memoria para optimizar performance
     */
    private static $codeCache = [];

    public static function getIdByCode(string $code): ?int
    {
        if (!isset(self::$codeCache[$code])) {
            self::$codeCache[$code] = static::where('code', $code)->value('id');
        }

        return self::$codeCache[$code];
    }

    /**
     * Limpia el caché de códigos (útil en tests o seeders)
     */
    public static function clearCodeCache(): void
    {
        self::$codeCache = [];
    }
}
