<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Attended = 'attended';
    case Absent = 'absent';
    case Cancelled = 'cancelled';

    /**
     * Valores válidos como array de strings
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Regla de validación Laravel: "in:scheduled,attended,absent,cancelled"
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
            self::Scheduled => 'Programado',
            self::Attended => 'Atendido',
            self::Absent => 'Ausente',
            self::Cancelled => 'Cancelado',
        };
    }

    /**
     * Etiqueta a partir del valor crudo (tolerante a valores desconocidos)
     */
    public static function labelFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->label() ?? 'Desconocido';
    }
}
