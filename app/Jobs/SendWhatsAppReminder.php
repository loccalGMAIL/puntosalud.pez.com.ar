<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class SendWhatsAppReminder implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries   = 2;
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

        // Revalidar que el turno sigue programado
        $appointment = $message->appointment;
        if (! $appointment || $appointment->status !== 'scheduled') {
            $message->markFailed('Turno cancelado o no disponible al momento del envío');
            return;
        }

        $result = $whatsAppService->sendMessage($message->phone, $message->message);

        if ($result['success']) {
            $message->markSent();
        } else {
            $message->markFailed($result['error'] ?? 'Error desconocido');
        }
    }

    public function failed(\Throwable $exception): void
    {
        $message = WhatsAppMessage::find($this->whatsappMessageId);
        $message?->markFailed('Job fallido: ' . $exception->getMessage());
    }
}
