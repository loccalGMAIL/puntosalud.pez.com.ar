<?php

namespace App\Services;

use Carbon\Carbon;

class WhatsAppDispatchWindow
{
    /**
     * Máximo de días que un envío puede adelantarse respecto al idealTime.
     * Acota la búsqueda hacia atrás y, en consecuencia, el horizonte de turnos
     * a considerar en el comando de envío.
     */
    public const ADVANCE_HORIZON_DAYS = 14;

    /**
     * Margen respecto al cierre de ventana para garantizar que el scheduler
     * (que corre cada 15 min) tenga al menos una corrida disponible antes
     * de que la ventana se cierre.
     */
    public const SCHEDULER_SAFE_CUTOFF_MINUTES = 20;

    /**
     * @param  array<int>  $sendDays  Carbon dayOfWeek: 0=domingo .. 6=sábado
     */
    public function __construct(
        private readonly array $sendDays,
        private readonly string $windowStart,
        private readonly string $windowEnd,
    ) {}

    public static function fromSettings(): self
    {
        $defaultDays = ['1', '2', '3', '4', '5', '6', '0'];

        $rawDays = setting('whatsapp.send_days', json_encode($defaultDays));
        $decoded = is_string($rawDays) ? json_decode($rawDays, true) : $rawDays;
        $days = is_array($decoded) ? $decoded : $defaultDays;

        $days = array_values(array_unique(array_map(static fn ($d) => (int) $d, $days)));
        $days = array_values(array_filter($days, static fn (int $d) => $d >= 0 && $d <= 6));

        return new self(
            $days,
            (string) setting('whatsapp.window_start', '09:00'),
            (string) setting('whatsapp.window_end', '21:00'),
        );
    }

    public function windowEnd(): string
    {
        return $this->windowEnd;
    }

    public function isAllowedAt(Carbon $moment): bool
    {
        if ($this->sendDays === []) {
            return false;
        }

        if (! in_array($moment->dayOfWeek, $this->sendDays, true)) {
            return false;
        }

        $start = $moment->copy()->setTimeFromTimeString($this->windowStart);
        $end = $moment->copy()->setTimeFromTimeString($this->windowEnd);

        // end es exclusivo
        return $moment->greaterThanOrEqualTo($start) && $moment->lessThan($end);
    }

    public function computeDispatchTime(Carbon $idealTime): Carbon
    {
        $idealTime = $idealTime->copy();

        if ($this->isAllowedAt($idealTime)) {
            return $idealTime;
        }

        $start = $idealTime->copy()->setTimeFromTimeString($this->windowStart);
        $end = $idealTime->copy()->setTimeFromTimeString($this->windowEnd);

        $dayAllowed = in_array($idealTime->dayOfWeek, $this->sendDays, true);

        // Día permitido pero fuera de horario
        if ($dayAllowed) {
            if ($idealTime->greaterThanOrEqualTo($end)) {
                return $end->subMinutes(self::SCHEDULER_SAFE_CUTOFF_MINUTES);
            }

            if ($idealTime->lessThan($start)) {
                $candidate = $idealTime->copy()->subDay();

                return $this->previousAllowedDaySafeCutoff($candidate);
            }
        }

        // Día bloqueado: retroceder hasta el último día permitido
        return $this->previousAllowedDaySafeCutoff($idealTime);
    }

    private function previousAllowedDaySafeCutoff(Carbon $from): Carbon
    {
        $probe = $from->copy();

        for ($i = 0; $i < self::ADVANCE_HORIZON_DAYS; $i++) {
            if (in_array($probe->dayOfWeek, $this->sendDays, true)) {
                $end = $probe->copy()->setTimeFromTimeString($this->windowEnd);

                return $end->subMinutes(self::SCHEDULER_SAFE_CUTOFF_MINUTES);
            }

            $probe->subDay();
        }

        // Config inválida (p.ej. sin días habilitados). Fallback seguro.
        return $from->copy();
    }
}
