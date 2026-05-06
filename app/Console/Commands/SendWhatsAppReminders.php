<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppDispatchWindow;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReminders extends Command
{
    protected $signature = 'whatsapp:send-reminders';

    protected $description = 'Busca turnos próximos y despacha los recordatorios de WhatsApp';

    public function handle(WhatsAppService $whatsAppService): int
    {
        if (! $whatsAppService->isEnabled()) {
            $this->info('Los recordatorios de WhatsApp están deshabilitados. Omitiendo.');

            return self::SUCCESS;
        }

        if (setting('whatsapp.send_reminders', '1') !== '1') {
            $this->info('El envío de recordatorios está desactivado en la configuración. Omitiendo.');

            return self::SUCCESS;
        }

        if (! $whatsAppService->isConnected()) {
            $this->warn('WhatsApp no está conectado. Omitiendo.');

            return self::SUCCESS;
        }

        $hoursBefore = (int) setting('whatsapp.hours_before', 24);
        $template = setting('whatsapp.template', '');
        $instance = setting('whatsapp.instance', '');

        $dispatchWindow = WhatsAppDispatchWindow::fromSettings();

        // Si el envío está fuera del rango permitido, no procesar nada (no generar "atrasos")
        if (! $dispatchWindow->isAllowedAt(now())) {
            $this->info('Fuera del horario/día permitido para envíos. Omitiendo.');

            return self::SUCCESS;
        }

        // -----------------------------------------------------------------------
        // Pass 1: recordatorios basados en hours_before
        // Considera turnos cuyo idealTime (appointment_date - hours_before) ya
        // alcanzó su dispatchTime calculado.
        // -----------------------------------------------------------------------
        $queryHorizon = now()->addHours($hoursBefore)->addMinutes(15)->addDays(WhatsAppDispatchWindow::ADVANCE_HORIZON_DAYS);

        $appointments = Appointment::scheduled()
            ->where('appointment_date', '>', now())
            ->where('appointment_date', '<=', $queryHorizon)
            ->whereHas('patient', fn ($q) => $q->whereNotNull('phone')->where('phone', '!=', ''))
            ->whereDoesntHave('whatsappMessages', fn ($q) => $q->where('type', 'reminder')->whereIn('status', ['sent', 'pending']))
            ->withCount([
                'whatsappMessages as reminder_failed_count' => fn ($q) => $q->where('type', 'reminder')->where('status', 'failed'),
            ])
            ->having('reminder_failed_count', '<', 3)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('whatsapp_opt_outs')
                    ->whereColumn('whatsapp_opt_outs.patient_id', 'appointments.patient_id')
                    ->whereColumn('whatsapp_opt_outs.professional_id', 'appointments.professional_id');
            })
            ->with(['patient', 'professional.specialty'])
            ->get();

        $dispatched = 0;
        $skipped = 0;

        foreach ($appointments as $appointment) {
            $idealTime = $appointment->appointment_date->copy()->subHours($hoursBefore);
            $dispatchTime = $dispatchWindow->computeDispatchTime($idealTime);

            // Todavía no llegó el momento efectivo de despacho
            if (now()->lt($dispatchTime)) {
                continue;
            }

            if ($this->sendReminderForAppointment($appointment, $whatsAppService, $instance, $template)) {
                $dispatched++;
            } else {
                $skipped++;
            }
        }

        // -----------------------------------------------------------------------
        // Pass 2: anticipación de turnos del día siguiente fuera de ventana
        // Turnos de mañana cuyo horario supera el cierre de la ventana de envío
        // (ej: turno a las 18:00, 19:00 cuando la ventana cierra a las 17:30).
        // Se despachan HOY durante la ventana activa para garantizar la entrega.
        // -----------------------------------------------------------------------
        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $nextDayPostWindow = Appointment::scheduled()
            ->whereBetween('appointment_date', [$tomorrowStart, $tomorrowEnd])
            ->whereRaw('TIME(appointment_date) >= ?', [$dispatchWindow->windowEnd()])
            ->whereHas('patient', fn ($q) => $q->whereNotNull('phone')->where('phone', '!=', ''))
            ->whereDoesntHave('whatsappMessages', fn ($q) => $q->where('type', 'reminder')->whereIn('status', ['sent', 'pending']))
            ->withCount([
                'whatsappMessages as reminder_failed_count' => fn ($q) => $q->where('type', 'reminder')->where('status', 'failed'),
            ])
            ->having('reminder_failed_count', '<', 3)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('whatsapp_opt_outs')
                    ->whereColumn('whatsapp_opt_outs.patient_id', 'appointments.patient_id')
                    ->whereColumn('whatsapp_opt_outs.professional_id', 'appointments.professional_id');
            })
            ->with(['patient', 'professional.specialty'])
            ->get();

        $advancedDispatched = 0;

        foreach ($nextDayPostWindow as $appointment) {
            if ($this->sendReminderForAppointment($appointment, $whatsAppService, $instance, $template)) {
                $advancedDispatched++;
            } else {
                $skipped++;
            }
        }

        // -----------------------------------------------------------------------
        // Pass de retry para creation/cancellation: failed con menos de 3 intentos
        // -----------------------------------------------------------------------
        $retryCandidates = WhatsAppMessage::query()
            ->whereIn('type', ['creation', 'cancellation'])
            ->where('status', 'failed')
            ->whereHas('appointment', fn ($q) => $q->where('appointment_date', '>', now()))
            ->select('appointment_id', 'type')
            ->selectRaw('COUNT(*) as attempts')
            ->groupBy('appointment_id', 'type')
            ->having('attempts', '<', 3)
            ->get();

        $retried = 0;

        foreach ($retryCandidates as $candidate) {
            try {
                $hasSentOrPending = WhatsAppMessage::where('appointment_id', $candidate->appointment_id)
                    ->where('type', $candidate->type)
                    ->whereIn('status', ['sent', 'pending'])
                    ->exists();
                if ($hasSentOrPending) {
                    continue;
                }

                $appointment = Appointment::with(['patient', 'professional.specialty'])
                    ->find($candidate->appointment_id);
                if (! $appointment) {
                    continue;
                }

                if ($whatsAppService->queueAppointmentMessage($appointment, $candidate->type)) {
                    $retried++;
                }
            } catch (\Throwable $e) {
                Log::error('WhatsApp retry candidate failed', [
                    'appointment_id' => $candidate->appointment_id,
                    'type' => $candidate->type,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info(
            "Reminders enviados: {$dispatched}"
            .(($advancedDispatched > 0) ? " (+ {$advancedDispatched} anticipados del día siguiente)" : '')
            .", fallidos/omitidos: {$skipped}, retries (creation/cancellation): {$retried}"
        );

        return self::SUCCESS;
    }

    private function sendReminderForAppointment(
        Appointment $appointment,
        WhatsAppService $whatsAppService,
        string $instance,
        string $template,
    ): bool {
        $patient = $appointment->patient;
        $formattedPhone = $whatsAppService->formatArgentinaPhone($patient->phone);

        if (! $formattedPhone) {
            $this->warn("Omitiendo turno #{$appointment->id}: teléfono inválido '{$patient->phone}'");

            return false;
        }

        $renderedMessage = $whatsAppService->renderTemplate($template, [
            'nombre' => $patient->full_name,
            'fecha' => $appointment->appointment_date->format('d/m/Y'),
            'hora' => $appointment->appointment_date->format('H:i'),
            'profesional' => $appointment->professional?->full_name ?? 'el/la profesional',
            'especialidad' => $appointment->professional?->specialty?->name ?? '',
        ]);

        $waMessage = WhatsAppMessage::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'phone' => $formattedPhone,
            'message' => $renderedMessage,
            'status' => 'pending',
            'instance' => $instance,
            'type' => 'reminder',
        ]);

        $result = $whatsAppService->sendMessage($waMessage->phone, $waMessage->message);

        if ($result['success']) {
            $waMessage->markSent();

            return true;
        }

        $friendly = match ($result['error'] ?? '') {
            'not_configured' => 'WhatsApp no está configurado correctamente.',
            default => 'No se pudo enviar el mensaje. Intentá nuevamente.',
        };
        $waMessage->markFailed($friendly);

        return false;
    }
}
