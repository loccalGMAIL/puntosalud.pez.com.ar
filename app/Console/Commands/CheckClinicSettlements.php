<?php

namespace App\Console\Commands;

use App\Models\CashMovement;
use App\Models\ProfessionalLiquidation;
use Illuminate\Console\Command;

class CheckClinicSettlements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'liquidations:check-clinic-settlements {--from= : Fecha desde (Y-m-d)} {--to= : Fecha hasta (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta liquidaciones con neto negativo (el profesional debe entregar al centro) cuya entrega no parece estar registrada como ingreso.';

    public function handle(): int
    {
        $query = ProfessionalLiquidation::with('professional')
            ->where('net_professional_amount', '<', 0)
            ->orderBy('liquidation_date');

        if ($this->option('from')) {
            $query->whereDate('liquidation_date', '>=', $this->option('from'));
        }
        if ($this->option('to')) {
            $query->whereDate('liquidation_date', '<=', $this->option('to'));
        }

        $liquidations = $query->get();

        if ($liquidations->isEmpty()) {
            $this->info('No hay liquidaciones con neto negativo en el rango indicado.');

            return self::SUCCESS;
        }

        $rows = [];
        $pendientes = 0;

        foreach ($liquidations as $liq) {
            $amount = abs((float) $liq->net_professional_amount);
            $profName = $liq->professional ? $liq->professional->full_name : ('#'.$liq->professional_id);

            // Estado estructurado (v2.12.7+)
            $status = $liq->clinic_settlement_status ?? 'not_required';

            // Heurística para liquidaciones legacy (status not_required): buscar un ingreso
            // (movimiento de caja positivo) del mismo monto exacto en la misma fecha. No se
            // filtra por nombre porque las descripciones son texto libre e inconsistentes
            // (apellidos compuestos, mayúsculas, "Compensación de Caja", etc.).
            $matchingIncome = CashMovement::whereDate('created_at', $liq->liquidation_date)
                ->where('amount', $amount)
                ->exists();

            $registrada = $status === 'settled' || $matchingIncome;

            if (! $registrada) {
                $pendientes++;
            }

            $rows[] = [
                'Liq #'.$liq->id,
                $liq->liquidation_date->format('Y-m-d'),
                $profName,
                '$'.number_format($amount, 0, ',', '.'),
                $status,
                $registrada ? 'OK' : '⚠ FALTA',
            ];
        }

        $this->table(
            ['Liquidación', 'Fecha', 'Profesional', 'Entrega', 'Estado', 'Ingreso'],
            $rows
        );

        if ($pendientes > 0) {
            $this->warn("Hay {$pendientes} liquidación(es) con la entrega al centro posiblemente SIN registrar. Revisar los marcados con ⚠ FALTA.");
        } else {
            $this->info('Todas las liquidaciones negativas tienen su entrega al centro registrada.');
        }

        return self::SUCCESS;
    }
}
