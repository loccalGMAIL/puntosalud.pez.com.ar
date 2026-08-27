<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAppointmentOffice extends Command
{
    protected $signature = 'appointments:backfill-consultorio'
        .' {--from= : Fecha desde (Y-m-d). Por defecto, 90 dias atras}'
        .' {--to= : Fecha hasta (Y-m-d). Por defecto, hoy}'
        .' {--dry-run : Muestra el detalle sin escribir nada}'
        .' {--force : Ejecuta sin pedir confirmacion}';

    protected $description = 'Completa el consultorio de turnos sin office_id usando el consultorio predeterminado del profesional';

    public function handle(): int
    {
        try {
            $from = $this->option('from')
                ? Carbon::createFromFormat('Y-m-d', $this->option('from'))->startOfDay()
                : Carbon::today()->subDays(90)->startOfDay();

            $to = $this->option('to')
                ? Carbon::createFromFormat('Y-m-d', $this->option('to'))->endOfDay()
                : Carbon::today()->endOfDay();
        } catch (\Exception) {
            $this->error('Las fechas --from y --to deben tener formato Y-m-d.');

            return self::FAILURE;
        }

        if ($from->gt($to)) {
            $this->error('La fecha --from no puede ser posterior a --to.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        // Sólo turnos sin consultorio cuyo profesional tenga uno predeterminado cargado.
        $appointments = Appointment::with(['professional.defaultOffice'])
            ->whereNull('office_id')
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('appointment_date', [$from, $to])
            ->whereHas('professional', fn ($q) => $q->whereNotNull('default_office_id'))
            ->orderBy('appointment_date')
            ->get();

        $this->line("Rango: {$from->format('Y-m-d')} a {$to->format('Y-m-d')}");

        if ($appointments->isEmpty()) {
            $this->info('No hay turnos para completar en ese rango.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn('ATENCION: esto es una INFERENCIA, no una restauracion.');
        $this->warn('El consultorio original no queda registrado en ninguna parte, asi que un turno');
        $this->warn('que realmente se dio en otro consultorio va a quedar mal asignado, y eso afecta');
        $this->warn('al reporte de ocupacion de consultorios. Revisa el detalle antes de confirmar.');
        $this->newLine();

        $rows = $appointments
            ->groupBy(fn ($a) => $a->professional->id)
            ->map(fn ($group) => [
                $group->first()->professional->last_name.', '.$group->first()->professional->first_name,
                $group->first()->professional->defaultOffice->name,
                $group->count(),
            ])
            ->values()
            ->all();

        $this->table(['Profesional', 'Consultorio a asignar', 'Turnos'], $rows);
        $this->line('Total de turnos a completar: '.$appointments->count());

        if ($dryRun) {
            $this->info('Dry-run: no se escribio nada.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Confirmas completar el consultorio de estos turnos?', false)) {
            $this->warn('Operacion cancelada.');

            return self::SUCCESS;
        }

        $updated = 0;

        DB::transaction(function () use ($appointments, &$updated): void {
            foreach ($appointments as $appointment) {
                $appointment->update(['office_id' => $appointment->professional->default_office_id]);
                $updated++;
            }
        });

        $this->info("Turnos actualizados: {$updated}");

        return self::SUCCESS;
    }
}
