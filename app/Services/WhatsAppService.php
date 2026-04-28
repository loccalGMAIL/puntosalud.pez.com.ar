<?php

namespace App\Services;

use App\Jobs\SendWhatsAppReminder;
use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Valida que WhatsApp esté habilitado y conectado.
     * Shape:
     * - ['ok' => true]
     * - ['ok' => false, 'error_code' => 'disabled'|'not_connected', 'message' => '...']
     */
    public function validateConnection(): array
    {
        if (! $this->isEnabled()) {
            return [
                'ok' => false,
                'error_code' => 'disabled',
                'message' => 'WhatsApp no está habilitado en el sistema.',
            ];
        }

        if (! $this->isConnected()) {
            return [
                'ok' => false,
                'error_code' => 'not_connected',
                'message' => 'WhatsApp no está conectado. Escaneá el QR e intentá nuevamente.',
            ];
        }

        return ['ok' => true];
    }

    /**
     * Normaliza y valida el teléfono del destinatario.
     * Shape:
     * - ['ok' => true, 'phone' => '549...']
     * - ['ok' => false, 'error_code' => 'no_phone'|'invalid_phone', 'message' => '...']
     */
    public function validateRecipient(?string $rawPhone): array
    {
        if (empty($rawPhone)) {
            return [
                'ok' => false,
                'error_code' => 'no_phone',
                'message' => 'El paciente no tiene un número de teléfono registrado.',
            ];
        }

        $formatted = $this->formatArgentinaPhone($rawPhone);

        if (! $formatted) {
            return [
                'ok' => false,
                'error_code' => 'invalid_phone',
                'message' => 'El número de teléfono no es válido. Verificá el formato (ej: 3541693286).',
            ];
        }

        return ['ok' => true, 'phone' => $formatted];
    }

    private function baseUrl(): string
    {
        return rtrim(setting('whatsapp.api_url', ''), '/');
    }

    private function instance(): string
    {
        return setting('whatsapp.instance', '');
    }

    private function headers(): array
    {
        return ['apikey' => setting('whatsapp.api_key', '')];
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl())
            && ! empty($this->instance())
            && ! empty($this->headers()['apikey']);
    }

    public function isEnabled(): bool
    {
        return setting('whatsapp.enabled', '0') === '1';
    }

    public function isConnected(): bool
    {
        $state = $this->connectionState();
        return ($state['state'] ?? '') === 'open';
    }

    /**
     * GET /instance/connectionState/{instance}
     */
    public function connectionState(): array
    {
        if (! $this->isConfigured()) {
            return ['state' => 'not_configured'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->baseUrl()}/instance/connectionState/{$this->instance()}");

            if (! $response->successful()) {
                Log::warning("WhatsApp connectionState: respuesta no exitosa", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ['state' => 'error', 'raw' => $response->body()];
            }

            $json = $response->json();

            if (! is_array($json)) {
                Log::warning("WhatsApp connectionState: respuesta no es JSON", ['raw' => $response->body()]);
                return ['state' => 'error', 'raw' => $response->body()];
            }

            // Evolution API v2 devuelve {"instance": {"state": "..."}}
            // Normalizamos para que siempre exista $json['state'] en la raíz
            if (! isset($json['state']) && isset($json['instance']['state'])) {
                $json['state'] = $json['instance']['state'];
            }

            Log::debug("WhatsApp connectionState: " . ($json['state'] ?? 'unknown'), ['json' => $json]);

            return $json;
        } catch (\Exception $e) {
            Log::error('WhatsApp connectionState failed', ['error' => $e->getMessage()]);
            return ['state' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Fuerza una sesión nueva: cierra credenciales guardadas y pide QR fresco.
     * Usar cuando la instancia está atascada en estado 'connecting' con creds inválidas.
     */
    public function forceNewQrCode(): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'not_configured'];
        }

        $instance = $this->instance();
        Log::info("WhatsApp forceNewQrCode: reiniciando instancia", ['instance' => $instance]);

        // Paso 1: Reiniciar Baileys sin eliminar la instancia ni su configuración
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(8)
                ->delete("{$this->baseUrl()}/instance/logout/{$instance}");
            Log::info("WhatsApp forceNewQrCode: DELETE /instance/logout (pre-restart)", [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
        } catch (\Exception $e) {
            Log::warning("WhatsApp forceNewQrCode: DELETE /instance/logout falló", ['error' => $e->getMessage()]);
        }

        // Paso 2: Restart — reinicia Baileys sin borrar la instancia
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(8)
                ->post("{$this->baseUrl()}/instance/restart/{$instance}");
            Log::info("WhatsApp forceNewQrCode: POST /instance/restart", [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
        } catch (\Exception $e) {
            Log::warning("WhatsApp forceNewQrCode: POST /instance/restart falló", ['error' => $e->getMessage()]);
        }

        // Paso 3: Logout inmediato — en la ventana de arranque antes de que Baileys recargue creds del disco
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(5)
                ->delete("{$this->baseUrl()}/instance/logout/{$instance}");
            Log::info("WhatsApp forceNewQrCode: DELETE /instance/logout (post-restart)", [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
        } catch (\Exception $e) {
            Log::warning("WhatsApp forceNewQrCode: DELETE /instance/logout (post-restart) falló", ['error' => $e->getMessage()]);
        }

        Log::info("WhatsApp forceNewQrCode: esperando 3s para estabilización...");
        sleep(3);

        Log::info("WhatsApp forceNewQrCode: solicitando QR...");
        return $this->getQrCode();
    }

    /**
     * GET /instance/connect/{instance}
     * Retorna ['base64' => '...'] cuando no está conectado, o ['state' => 'open'] cuando ya lo está.
     */
    public function getQrCode(): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'not_configured'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl()}/instance/connect/{$this->instance()}");

            $keys = array_keys($response->json() ?? []);
            Log::info("WhatsApp getQrCode: GET /instance/connect", [
                'status'    => $response->status(),
                'keys'      => $keys,
                'has_base64' => in_array('base64', $keys),
                'body_snippet' => substr($response->body(), 0, 200),
            ]);

            return $response->successful()
                ? $response->json()
                : ['error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('WhatsApp getQrCode failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * DELETE /instance/logout/{instance}
     * Retorna ['success' => bool, 'message' => string]
     *
     * Evolution API v2 requiere que el socket esté en estado 'open' para logout.
     * Si está 'close', intenta reconectar primero (Baileys usa credenciales guardadas).
     */
    public function disconnect(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'not_configured'];
        }

        $state = $this->connectionState();

        // Socket abierto: logout directo
        if (($state['state'] ?? '') === 'open') {
            return $this->doLogout();
        }

        // Socket cerrado: intentar que Baileys reconecte con las credenciales guardadas
        try {
            Http::withHeaders($this->headers())
                ->timeout(5)
                ->get("{$this->baseUrl()}/instance/connect/{$this->instance()}");
        } catch (\Exception) {}

        // Esperar hasta 5s a que el socket vuelva a 'open'
        for ($i = 0; $i < 5; $i++) {
            sleep(1);
            $state = $this->connectionState();
            if (($state['state'] ?? '') === 'open') {
                return $this->doLogout();
            }
        }

        return ['success' => false, 'message' => 'socket_closed'];
    }

    /**
     * Ejecutar el logout asumiendo que el socket está abierto.
     */
    private function doLogout(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->delete("{$this->baseUrl()}/instance/logout/{$this->instance()}");

            if (! $response->successful()) {
                Log::warning('WhatsApp logout returned error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            Log::error('WhatsApp logout failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'exception'];
        }
    }

    /**
     * POST /message/sendText/{instance}
     */
    public function sendMessage(string $number, string $text): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'not_configured'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post("{$this->baseUrl()}/message/sendText/{$this->instance()}", [
                    'number' => $number,
                    'text'   => $text,
                ]);

            if (! $response->successful()) {
                Log::error('WhatsApp Evolution API error (sendText)', [
                    'url'    => "{$this->baseUrl()}/message/sendText/{$this->instance()}",
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
                // No exponer el body crudo al usuario. El detalle queda en logs.
                'error'   => $response->successful() ? null : 'send_failed',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp sendMessage failed', [
                'number' => $number,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'error' => 'exception'];
        }
    }

    /**
     * Formatea un número de teléfono argentino al formato internacional para WhatsApp.
     * Retorna null si no se puede normalizar.
     *
     * Formatos soportados:
     * - 549XXXXXXXXXX   → ya OK (12+ dígitos con 549)
     * - 54XXXXXXXXXX    → insertar 9: 549...
     * - 10 dígitos      → prefijo 549
     * - 11 dígitos con 0 inicial → remover 0, prefijo 549
     */
    public function formatArgentinaPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        // Ya en formato 549XXXXXXXXXX
        if (str_starts_with($digits, '549') && strlen($digits) >= 12) {
            return $digits;
        }

        // Tiene código de país 54 pero sin el 9 del móvil
        if (str_starts_with($digits, '54') && strlen($digits) >= 12) {
            return '549' . substr($digits, 2);
        }

        // 10 dígitos (área + número, sin 0 inicial)
        if (strlen($digits) === 10) {
            return '549' . $digits;
        }

        // 11 dígitos con 0 inicial (p.ej. 0114234-5678)
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '549' . substr($digits, 1);
        }

        return null;
    }

    /**
     * Encola un mensaje de WhatsApp para un turno según el tipo indicado ('reminder' o 'creation').
     * Verifica habilitación, conexión, teléfono válido y opt-out antes de crear el registro y despachar el job.
     * Retorna true si se encoló, false si se omitió por cualquier condición.
     */
    public function queueAppointmentMessage(Appointment $appointment, string $type): bool
    {
        if (! $this->isEnabled()) {
            Log::info('WhatsApp skipped', ['reason' => 'disabled', 'appointment_id' => $appointment->id, 'type' => $type]);
            return false;
        }

        if (! $this->isConnected()) {
            Log::info('WhatsApp skipped', ['reason' => 'not_connected', 'appointment_id' => $appointment->id, 'type' => $type]);
            return false;
        }

        // Asegurarse de tener las relaciones cargadas
        $appointment->loadMissing(['patient', 'professional.specialty']);

        $patient = $appointment->patient;

        if (! $patient || empty($patient->phone)) {
            Log::info('WhatsApp skipped', ['reason' => 'no_phone', 'appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id, 'type' => $type]);
            return false;
        }

        // Verificar opt-out del paciente para este profesional
        $hasOptOut = DB::table('whatsapp_opt_outs')
            ->where('patient_id', $appointment->patient_id)
            ->where('professional_id', $appointment->professional_id)
            ->exists();

        if ($hasOptOut) {
            Log::info('WhatsApp skipped', ['reason' => 'opt_out', 'appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id, 'professional_id' => $appointment->professional_id, 'type' => $type]);
            return false;
        }

        $formattedPhone = $this->formatArgentinaPhone($patient->phone);
        if (! $formattedPhone) {
            Log::info('WhatsApp skipped', ['reason' => 'invalid_phone', 'appointment_id' => $appointment->id, 'phone' => $patient->phone, 'type' => $type]);
            return false;
        }

        // Evitar duplicados solo si ya hay un envío exitoso o en curso para ese turno/tipo.
        // Filas en estado 'failed' deben permitir reintentos (las hacía el unique index antes).
        $alreadyExists = WhatsAppMessage::query()
            ->where('appointment_id', $appointment->id)
            ->where('type', $type)
            ->whereIn('status', ['sent', 'pending'])
            ->exists();
        if ($alreadyExists) {
            Log::info('WhatsApp skipped', ['reason' => 'already_exists', 'appointment_id' => $appointment->id, 'type' => $type]);
            return false;
        }

        $templateKey = match ($type) {
            'creation'     => 'whatsapp.template_on_create',
            'cancellation' => 'whatsapp.template_on_cancel',
            default        => 'whatsapp.template',
        };
        $template    = setting($templateKey, '');

        if (empty($template)) {
            Log::info('WhatsApp skipped', ['reason' => 'missing_template', 'appointment_id' => $appointment->id, 'type' => $type]);
            return false;
        }

        $renderedMessage = $this->renderTemplate($template, [
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
            'instance'       => setting('whatsapp.instance', ''),
            'type'           => $type,
        ]);

        SendWhatsAppReminder::dispatchSync($waMessage->id);

        return true;
    }

    /**
     * Envía un mensaje de turno de forma sincrónica (sin pasar por el job).
     * Mantiene las validaciones de queueAppointmentMessage, pero no revalida el status del turno.
     *
     * Retorna: ['success' => bool, 'message' => string]
     */
    public function sendAppointmentMessageNow(Appointment $appointment, string $type): array
    {
        $conn = $this->validateConnection();
        if (! ($conn['ok'] ?? false)) {
            return ['success' => false, 'message' => $conn['message'] ?? 'WhatsApp no está disponible.'];
        }

        $appointment->loadMissing(['patient', 'professional.specialty']);

        $patient = $appointment->patient;

        $recipient = $this->validateRecipient($patient?->phone);
        if (! ($recipient['ok'] ?? false)) {
            return ['success' => false, 'message' => $recipient['message'] ?? 'El número de teléfono no es válido.'];
        }

        $hasOptOut = DB::table('whatsapp_opt_outs')
            ->where('patient_id', $appointment->patient_id)
            ->where('professional_id', $appointment->professional_id)
            ->exists();

        if ($hasOptOut) {
            return ['success' => false, 'message' => 'El paciente optó por no recibir mensajes'];
        }

        $formattedPhone = $recipient['phone'];

        $templateKey = match ($type) {
            'creation'     => 'whatsapp.template_on_create',
            'cancellation' => 'whatsapp.template_on_cancel',
            default        => 'whatsapp.template',
        };
        $template = setting($templateKey, '');

        if (empty($template)) {
            return ['success' => false, 'message' => 'Plantilla no configurada'];
        }

        $renderedMessage = $this->renderTemplate($template, [
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
            'instance'       => setting('whatsapp.instance', ''),
            'type'           => $type,
        ]);

        $result = $this->sendMessage($formattedPhone, $renderedMessage);

        if ($result['success']) {
            $waMessage->markSent();
            return ['success' => true, 'message' => 'Recordatorio enviado'];
        }

        $friendly = match ($result['error'] ?? '') {
            'not_configured' => 'WhatsApp no está configurado correctamente.',
            default          => 'No se pudo enviar el mensaje. Intentá nuevamente.',
        };

        // Guardar error legible (no body crudo/stack trace)
        $waMessage->markFailed($friendly);
        return ['success' => false, 'message' => $friendly];
    }

    /**
     * POST /message/sendMedia/{instance}
     */
    public function sendMediaFile(string $number, string $base64, string $filename, string $caption = ''): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'not_configured'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(60)
                ->post("{$this->baseUrl()}/message/sendMedia/{$this->instance()}", [
                    'number'    => $number,
                    'mediatype' => 'document',
                    'media'     => $base64,
                    'fileName'  => $filename,
                    'caption'   => $caption,
                ]);

            if (! $response->successful()) {
                Log::error('WhatsApp Evolution API error (sendMedia)', [
                    'url'    => "{$this->baseUrl()}/message/sendMedia/{$this->instance()}",
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
                'error'   => $response->successful() ? null : 'send_failed',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp sendMediaFile failed', [
                'number' => $number,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'error' => 'exception'];
        }
    }

    /**
     * Renderiza el template sustituyendo {{variable}} con los valores dados.
     */
    public function renderTemplate(string $template, array $variables): string
    {
        $search  = [];
        $replace = [];

        foreach ($variables as $key => $value) {
            $search[]  = '{{' . $key . '}}';
            $replace[] = (string) $value;
        }

        return str_replace($search, $replace, $template);
    }
}
