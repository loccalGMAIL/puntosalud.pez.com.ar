<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppDispatchWindow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WhatsAppRemindersStatus extends Command
{
    protected $signature = 'whatsapp:reminders-status
                            {--limit=50 : Cuántos turnos mostrar (default 50)}
                            {--all : Sin límite}
                            {--json : Salida JSON en lugar de tabla}';

    protected $description = 'Lista turnos pendientes de envío de recordatorio, con su dispatchTime calculado.';

    public function handle(): int
    {
        $hoursBefore = (int) setting('whatsapp.hours_before', 24);
        $window = WhatsAppDispatchWindow::fromSettings();

        $horizon = now()
            ->addHours($hoursBefore)
            ->addMinutes(15)
            ->addDays(WhatsAppDispatchWindow::ADVANCE_HORIZON_DAYS);

        $query = Appointment::scheduled()
            ->where('appointment_date', '>', now())
            ->where('appointment_date', '<=', $horizon)
            ->whereHas('patient', fn ($q) => $q->whereNotNull('phone')->where('phone', '!=', ''))
            ->whereDoesntHave('whatsappMessages', fn ($q) => $q->where('type', 'reminder')->whereIn('status', ['sent', 'pending']))
            ->withCount([
                'whatsappMessages as reminder_failed_count' => fn ($q) => $q->where('type', 'reminder')->where('status', 'failed'),
            ])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('whatsapp_opt_outs')
                    ->whereColumn('whatsapp_opt_outs.patient_id', 'appointments.patient_id')
                    ->whereColumn('whatsapp_opt_outs.professional_id', 'appointments.professional_id');
            })
            ->with(['patient:id,first_name,last_name,phone', 'professional:id,first_name,last_name'])
            ->orderBy('appointment_date');

        if (! $this->option('all')) {
            $query->limit((int) $this->option('limit'));
        }

        $appointments = $query->get();

        $now = now();

        $rows = $appointments->map(function ($a) use ($hoursBefore, $window, $now) {
            $ideal = $a->appointment_date->copy()->subHours($hoursBefore);
            $dispatch = $window->computeDispatchTime($ideal);
            $excluded = $a->reminder_failed_count >= 3;

            $status = match (true) {
                $excluded                  => 'EXCLUDED (3 fails)',
                $now->greaterThanOrEqualTo($dispatch) => 'OVERDUE → próxima ventana',
                default                    => 'queued → '.$dispatch->diffForHumans($now, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]),
            };

            return [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date->toDateTimeString(),
                'ideal_time' => $ideal->toDateTimeString(),
                'dispatch_time' => $dispatch->toDateTimeString(),
                'professional' => trim(($a->professional?->first_name ?? '').' '.($a->professional?->last_name ?? '')),
                'patient' => trim(($a->patient?->first_name ?? '').' '.($a->patient?->last_name ?? '')),
                'phone' => $a->patient?->phone,
                'failed_reminders' => $a->reminder_failed_count,
                'status' => $status,
            ];
        })->all();

        if ($this->option('json')) {
            $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Pendientes: %d (now=%s, hours_before=%d, window=%s-%s)',
            count($rows),
            $now->toDateTimeString(),
            $hoursBefore,
            (string) setting('whatsapp.window_start', '09:00'),
            (string) setting('whatsapp.window_end', '21:00'),
        ));

        if (empty($rows)) {
            return self::SUCCESS;
        }

        $this->table(
            ['id', 'appt_date', 'idealTime', 'dispatchTime', 'profesional', 'paciente', 'tel', 'fail', 'status'],
            collect($rows)->map(fn ($r) => [
                $r['id'],
                $r['appointment_date'],
                $r['ideal_time'],
                $r['dispatch_time'],
                substr($r['professional'], 0, 25),
                substr($r['patient'], 0, 25),
                $r['phone'],
                $r['failed_reminders'],
                $r['status'],
            ])->all(),
        );

        return self::SUCCESS;
    }
}
