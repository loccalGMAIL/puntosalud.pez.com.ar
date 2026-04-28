<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class SendWhatsAppReminder implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        private readonly int $whatsappMessageId
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $message = WhatsAppMessage::find($this->whatsappMessageId);

        if (! $message || $message->status !== 'pending') {
            return;
        }

        // Revalidar estado del turno según tipo de mensaje
        $appointment = $message->appointment;
        $expectedStatus = $message->type === 'cancellation' ? 'cancelled' : 'scheduled';
        if (! $appointment || $appointment->status !== $expectedStatus) {
            $message->markFailed('Estado del turno inválido al momento del envío');

            return;
        }

        // Re-consultar el teléfono actual del paciente (puede haber cambiado desde que se creó el row)
        $appointment->loadMissing('patient');
        $livePhone = $whatsAppService->formatArgentinaPhone(
            $appointment->patient?->phone ?? ''
        );

        if (! $livePhone) {
            $message->markFailed('El paciente no tiene un número de teléfono válido.');

            return;
        }

        if ($livePhone !== $message->phone) {
            $message->phone = $livePhone;
            $message->save();
        }

        $result = $whatsAppService->sendMessage($message->phone, $message->message);

        if ($result['success']) {
            $message->markSent();
        } else {
            $friendly = match ($result['error'] ?? '') {
                'not_configured' => 'WhatsApp no está configurado correctamente.',
                default => 'No se pudo enviar el mensaje. Intentá nuevamente.',
            };
            $message->markFailed($friendly);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $message = WhatsAppMessage::find($this->whatsappMessageId);
        $message?->markFailed('Job fallido: '.$exception->getMessage());
    }
}
