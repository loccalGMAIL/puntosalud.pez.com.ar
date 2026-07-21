<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    use HasFactory, LogsActivity;

    public function activityDescription(): string
    {
        $type = $this->movementType()->first();
        $typeName = $type ? $type->name : 'Movimiento';
        $base = $typeName.' #'.$this->id;

        if ($this->reference_type && str_ends_with($this->reference_type, 'Payment') && $this->reference_id) {
            $payment = \App\Models\Payment::find($this->reference_id);
            if ($payment?->receipt_number) {
                return $base.' - Recibo #'.$payment->receipt_number;
            }
        }

        if ($this->description) {
            return $base.': '.\Illuminate\Support\Str::limit($this->description, 50);
        }

        return $base;
    }

    protected $fillable = [
        'movement_type_id',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'balance_after',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    // Relaciones

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movementType()
    {
        return $this->belongsTo(MovementType::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes

    /**
     * Filtrar por código de tipo de movimiento
     *
     * @param  string  $typeCode  Código del tipo (ej: 'patient_payment', 'expense', etc.)
     */
    public function scopeByType($query, $typeCode)
    {
        return $query->whereHas('movementType', function ($q) use ($typeCode) {
            $q->where('code', $typeCode);
        });
    }

    /**
     * Filtrar por ID de tipo de movimiento
     */
    public function scopeByTypeId($query, $typeId)
    {
        return $query->where('movement_type_id', $typeId);
    }

    public function scopeIncome($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeExpense($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeOpeningMovements($query)
    {
        return $query->whereHas('movementType', function ($q) {
            $q->where('code', 'cash_opening');
        });
    }

    public function scopeClosingMovements($query)
    {
        return $query->whereHas('movementType', function ($q) {
            $q->where('code', 'cash_closing');
        });
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    // Métodos helper

    public function isIncome()
    {
        return $this->amount > 0;
    }

    public function isExpense()
    {
        return $this->amount < 0;
    }

    public function isOpening()
    {
        return $this->movementType?->code === 'cash_opening';
    }

    public function isClosing()
    {
        return $this->movementType?->code === 'cash_closing';
    }

    // Presentación del concepto (para las vistas de caja y reportes)

    /**
     * Título principal del concepto, sin el tipo de movimiento ni el medio de pago.
     * - Pago/reembolso de paciente => nombre del paciente.
     * - Liquidación profesional     => nombre del profesional.
     * - Gastos/retiros/ingresos/apertura/cierre => la descripción tal cual.
     */
    public function conceptTitle(): string
    {
        $ref = $this->reference;

        if ($ref instanceof Payment) {
            return $ref->patient?->full_name ?? $this->description;
        }

        if ($ref instanceof ProfessionalLiquidation) {
            return $ref->professional?->full_name ?? $this->description;
        }

        return $this->description;
    }

    /**
     * Nombre del profesional asociado al movimiento, para mostrar como sub-línea.
     * Devuelve null cuando no aplica (o cuando el profesional ya es el título).
     */
    public function conceptProfessionalName(): ?string
    {
        $ref = $this->reference;

        if ($ref instanceof Payment) {
            if ($ref->payment_type === 'refund') {
                return null;
            }

            $names = $ref->paymentAppointments
                ->map(fn ($pa) => $pa->appointment?->professional?->full_name)
                ->filter()
                ->unique()
                ->values();

            return $names->isNotEmpty() ? $names->join(', ') : null;
        }

        if ($ref instanceof Professional) {
            return $ref->full_name;
        }

        return null;
    }

    /**
     * Datos extra para movimientos de reembolso/reintegro a paciente:
     * número de recibo original y #id del movimiento de caja que se anula.
     * Devuelve null si el movimiento no es un reembolso.
     */
    public function refundInfo(): ?array
    {
        $ref = $this->reference;

        if (! $ref instanceof Payment || $ref->payment_type !== 'refund') {
            return null;
        }

        $receipt = null;
        if (preg_match('/#(\d+)/', (string) $ref->concept, $matches)) {
            $receipt = $matches[1];
        }

        $originalMovementId = null;
        if ($receipt !== null) {
            $originalPayment = Payment::where('receipt_number', $receipt)->first();

            if ($originalPayment) {
                $originalMovementId = static::where('reference_type', Payment::class)
                    ->where('reference_id', $originalPayment->id)
                    ->where('amount', '>', 0)
                    ->orderBy('id')
                    ->value('id');
            }
        }

        return [
            'receipt' => $receipt,
            'original_movement_id' => $originalMovementId,
        ];
    }

    public static function getCashStatusForDate($date)
    {
        $opening = static::forDate($date)->openingMovements()->first();
        $closing = static::forDate($date)->closingMovements()->first();

        return [
            'is_open' => $opening && ! $closing,
            'is_closed' => $opening && $closing,
            'needs_opening' => ! $opening,
            'opening_movement' => $opening,
            'closing_movement' => $closing,
        ];
    }

    public static function hasUnclosedCash()
    {
        // Obtener el ID del tipo 'cash_closing'
        $closingTypeId = MovementType::where('code', 'cash_closing')->value('id');

        // Buscar días con apertura pero sin cierre
        $unclosedDates = static::openingMovements()
            ->whereNotExists(function ($query) use ($closingTypeId) {
                $query->select('id')
                    ->from('cash_movements as cm2')
                    ->whereRaw('DATE(cm2.created_at) = DATE(cash_movements.created_at)')
                    ->where('cm2.movement_type_id', $closingTypeId);
            })
            ->where('created_at', '<', now()->startOfDay())
            ->orderBy('created_at', 'desc')
            ->first();

        return $unclosedDates ? $unclosedDates->created_at->format('Y-m-d') : null;
    }

    /**
     * Obtiene el balance de caja con lock pesimista para evitar condiciones de carrera
     *
     * @param  mixed  $asOfDate  Fecha límite opcional (inclusive). Si se pasa, devuelve el balance
     *                           al último movimiento de esa fecha o anterior. Sin fecha, el actual.
     * @return float El balance de caja
     */
    public static function getCurrentBalanceWithLock($asOfDate = null)
    {
        $query = static::query();

        if ($asOfDate !== null) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($asOfDate));
        }

        $lastMovement = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        return $lastMovement ? $lastMovement->balance_after : 0;
    }

    /**
     * Verifica si la caja está abierta para hoy
     *
     * @return bool True si la caja está abierta, False si está cerrada o no abierta
     */
    public static function isCashOpenToday()
    {
        $status = static::getCashStatusForDate(now());

        return $status['is_open'];
    }
}
