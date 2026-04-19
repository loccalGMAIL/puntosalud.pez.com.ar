<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppReminder;
use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendWhatsAppReminders extends Command
{
    protected $signature   = 'whatsapp:send-reminders';
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
        $template    = setting('whatsapp.template', '');
        $instance    = setting('whatsapp.instance', '');

        // Ventana de 15 minutos centrada en el momento exacto del recordatorio
        $windowStart = now()->addHours($hoursBefore);
        $windowEnd   = $windowStart->copy()->addMinutes(15);

        $appointments = Appointment::scheduled()
            ->whereBetween('appointment_date', [$windowStart, $windowEnd])
            ->whereHas('patient', fn ($q) => $q->whereNotNull('phone')->where('phone', '!=', ''))
            ->whereDoesntHave('whatsappMessages', fn ($q) => $q->where('type', 'reminder'))
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('whatsapp_opt_outs')
                    ->whereColumn('whatsapp_opt_outs.patient_id', 'appointments.patient_id')
                    ->whereColumn('whatsapp_opt_outs.professional_id', 'appointments.professional_id');
            })
            ->with(['patient', 'professional.specialty'])
            ->get();

        $dispatched = 0;
        $skipped    = 0;

        foreach ($appointments as $appointment) {
            $patient        = $appointment->patient;
            $formattedPhone = $whatsAppService->formatArgentinaPhone($patient->phone);

            if (! $formattedPhone) {
                $this->warn("Omitiendo turno #{$appointment->id}: teléfono inválido '{$patient->phone}'");
                $skipped++;
                continue;
            }

            $renderedMessage = $whatsAppService->renderTemplate($template, [
                'nombre'       => $patient->full_name,
                'fecha'        => $appointment->appointment_date->format('d/m/Y'),
                'hora'         => $appointment->appointment_date->format('H:i'),
                'profesional'  => $appointment->professional?->full_name ?? 'el/la profesional',
                'especialidad' => $appointment->professional?->specialty?->name ?? '',
            ]);

            $waMessage = WhatsAppMessage::create([
                'appointment_id' => $appointment->id,
                'patient_id'     => $patient->id,
                'phone'          => $formattedPhone,
                'message'        => $renderedMessage,
                'status'         => 'pending',
                'instance'       => $instance,
            ]);

            SendWhatsAppReminder::dispatch($waMessage->id);
            $dispatched++;
        }

        $this->info("Despachados: {$dispatched}, Omitidos: {$skipped}");
        return self::SUCCESS;
    }
}
