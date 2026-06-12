<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case DebitCard = 'debit_card';
    case CreditCard = 'credit_card';
    case Qr = 'qr';

    /**
     * Valores válidos como array de strings
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Regla de validación Laravel: "in:cash,transfer,debit_card,credit_card,qr"
     */
    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    /**
     * Etiqueta en español
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Transfer => 'Transferencia',
            self::DebitCard => 'Débito',
            self::CreditCard => 'Crédito',
            self::Qr => 'QR',
        };
    }

    /**
     * Etiqueta a partir del valor crudo (tolerante a valores legacy como 'other')
     */
    public static function labelFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->label() ?? match ($value) {
            'other' => 'Otro',
            default => ucfirst((string) $value),
        };
    }
}
