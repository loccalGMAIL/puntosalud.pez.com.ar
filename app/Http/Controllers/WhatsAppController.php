<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Services\SettingService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(
        private readonly WhatsAppService $whatsApp,
        private readonly SettingService  $settings,
    ) {}

    /**
     * GET /whatsapp — Página de conexión / QR
     */
    public function index(): View
    {
        $state        = $this->whatsApp->connectionState();
        $isConnected  = ($state['state'] ?? '') === 'open';
        $isConfigured = $this->whatsApp->isConfigured();
        $features     = [
            'send_reminders' => setting('whatsapp.send_reminders', '1'),
            'send_on_create' => setting('whatsapp.send_on_create', '0'),
            'send_on_cancel' => setting('whatsapp.send_on_cancel', '0'),
        ];

        return view('whatsapp.index', compact('state', 'isConnected', 'isConfigured', 'features'));
    }

    /**
     * GET /whatsapp/qr-code — Polling Ajax: retorna QR o estado conectado
     */
    public function qrCode(Request $request): JsonResponse
    {
        // Verificar estado primero — si ya está conectado no llamar a getQrCode()
        // (llamar a /instance/connect en una instancia activa puede interferir)
        $connState = $this->whatsApp->connectionState();
        $state     = $connState['state'] ?? 'unknown';

        // Conectado: retornar inmediatamente sin pedir QR
        if ($state === 'open') {
            return response()->json(['connected' => true, 'qr' => null, 'state' => 'open']);
        }

        // Conectando: Baileys está reconectando con credenciales guardadas.
        // NO llamar a getQrCode() — interrumpiría el proceso de reconexión.
        // Excepción: ?force=1 permite forzar QR (instancia nueva o sesión rota).
        if ($state === 'connecting' && ! $request->boolean('force')) {
            return response()->json(['connected' => false, 'qr' => null, 'state' => 'connecting']);
        }

        // Force=1: limpiar sesión guardada y pedir QR fresco (rompe el ciclo 'connecting')
        // Sin force: pedir QR directo (instancia sin credenciales previas)
        $qrData = $request->boolean('force')
            ? $this->whatsApp->forceNewQrCode()
            : $this->whatsApp->getQrCode();

        // Evolution API puede devolver el base64 con o sin el prefijo data:URI
        $qr = $qrData['base64'] ?? null;
        if ($qr && str_starts_with($qr, 'data:')) {
            $qr = substr($qr, strpos($qr, ',') + 1);
        }

        return response()->json(['connected' => false, 'qr' => $qr, 'state' => $state]);
    }

    /**
     * GET /whatsapp/status — Polling Ajax: sólo estado de conexión
     */
    public function connectionStatus(): JsonResponse
    {
        $state = $this->whatsApp->connectionState();
        return response()->json([
            'connected' => ($state['state'] ?? '') === 'open',
            'state'     => $state['state'] ?? 'unknown',
        ]);
    }

    /**
     * POST /whatsapp/disconnect
     */
    public function disconnect(): RedirectResponse
    {
        $result = $this->whatsApp->disconnect();

        if ($result['success']) {
            return redirect()->route('whatsapp.index')
                ->with('success', 'Sesión de WhatsApp cerrada correctamente.');
        }

        $msg = match ($result['message'] ?? '') {
            'socket_closed' => 'No se pudo cerrar la sesión automáticamente. Para desvincular el dispositivo, andá en tu teléfono a WhatsApp → Ajustes → Dispositivos vinculados y eliminá este dispositivo.',
            default         => 'No se pudo cerrar la sesión de WhatsApp.',
        };

        return redirect()->route('whatsapp.index')->with('error', $msg);
    }

    /**
     * POST /whatsapp/feature — Guarda un toggle individual vía AJAX
     */
    public function toggleFeature(Request $request): JsonResponse
    {
        $allowed = ['enabled', 'send_reminders', 'send_on_create', 'send_on_cancel'];

        $validated = $request->validate([
            'key'   => 'required|in:' . implode(',', $allowed),
            'value' => 'required|in:0,1',
        ]);

        $this->settings->set('whatsapp.' . $validated['key'], 'whatsapp', $validated['value']);

        return response()->json(['success' => true]);
    }

    /**
     * POST /whatsapp/features — Guarda los toggles de funciones desde la página de conexión
     */
    public function saveFeatures(Request $request): RedirectResponse
    {
        $group = 'whatsapp';
        $this->settings->set('whatsapp.send_reminders', $group, $request->input('send_reminders') === '1' ? '1' : '0');
        $this->settings->set('whatsapp.send_on_create', $group, $request->input('send_on_create')  === '1' ? '1' : '0');
        $this->settings->set('whatsapp.send_on_cancel', $group, $request->input('send_on_cancel')  === '1' ? '1' : '0');

        return redirect()->route('whatsapp.index')
            ->with('success', 'Funciones actualizadas correctamente.');
    }

    /**
     * GET /whatsapp/settings — Configuración del recordatorio (mensaje y timing)
     */
    public function settings(): View
    {
        $defaultDaysJson = json_encode(['1', '2', '3', '4', '5', '6', '0']);
        $sendDaysRaw = setting('whatsapp.send_days', $defaultDaysJson);
        $sendDays = is_string($sendDaysRaw) ? json_decode($sendDaysRaw, true) : $sendDaysRaw;
        if (! is_array($sendDays)) {
            $sendDays = ['1', '2', '3', '4', '5', '6', '0'];
        }

        $current = [
            'hours_before'       => setting('whatsapp.hours_before', '24'),
            'template'           => setting('whatsapp.template', ''),
            'template_on_create' => setting('whatsapp.template_on_create', ''),
            'template_on_cancel' => setting('whatsapp.template_on_cancel', ''),
            'send_days'          => $sendDays,
            'window_start'       => setting('whatsapp.window_start', '09:00'),
            'window_end'         => setting('whatsapp.window_end', '21:00'),
        ];

        return view('whatsapp.settings', compact('current'));
    }

    /**
     * POST /whatsapp/settings
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hours_before'       => 'required|in:1,2,4,12,24,48',
            'template'           => 'nullable|string|max:1000',
            'template_on_create' => 'nullable|string|max:1000',
            'template_on_cancel' => 'nullable|string|max:1000',
            'send_days'          => 'required|array|min:1',
            'send_days.*'        => 'in:0,1,2,3,4,5,6',
            'window_start'       => 'required|date_format:H:i',
            'window_end'         => 'required|date_format:H:i',
        ], [
            'hours_before.required'  => 'Seleccioná con cuánta anticipación enviar el recordatorio.',
            'hours_before.in'        => 'El valor de anticipación no es válido.',
            'template.max'           => 'El mensaje de recordatorio no puede superar los 1000 caracteres.',
            'template_on_create.max' => 'El mensaje de confirmación no puede superar los 1000 caracteres.',
            'template_on_cancel.max' => 'El mensaje de cancelación no puede superar los 1000 caracteres.',
            'send_days.required'      => 'Seleccioná al menos un día habilitado para el envío.',
            'send_days.array'         => 'La selección de días no es válida.',
            'send_days.min'           => 'Seleccioná al menos un día habilitado para el envío.',
            'send_days.*.in'          => 'La selección de días no es válida.',
            'window_start.required'   => 'Ingresá la hora mínima de envío.',
            'window_start.date_format'=> 'La hora mínima debe tener el formato HH:MM.',
            'window_end.required'     => 'Ingresá la hora máxima de envío.',
            'window_end.date_format'  => 'La hora máxima debe tener el formato HH:MM.',
        ]);

        // Validación cruzada: window_end > window_start
        if (strcmp($validated['window_end'], $validated['window_start']) <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['window_end' => 'La hora máxima debe ser mayor a la hora mínima.']);
        }

        $group = 'whatsapp';
        $this->settings->set('whatsapp.hours_before',       $group, $validated['hours_before']);
        $this->settings->set('whatsapp.template',           $group, $validated['template'] ?? '');
        $this->settings->set('whatsapp.template_on_create', $group, $validated['template_on_create'] ?? '');
        $this->settings->set('whatsapp.template_on_cancel', $group, $validated['template_on_cancel'] ?? '');
        $this->settings->set('whatsapp.send_days',          $group, json_encode(array_values($validated['send_days'])));
        $this->settings->set('whatsapp.window_start',       $group, $validated['window_start']);
        $this->settings->set('whatsapp.window_end',         $group, $validated['window_end']);

        return redirect()->route('whatsapp.settings')
            ->with('success', 'Configuración del recordatorio guardada.');
    }

    /**
     * GET /whatsapp/api — Configuración de la API (URL, key, instancia, habilitado)
     */
    public function apiSettings(): View
    {
        $current = [
            'enabled'  => setting('whatsapp.enabled', '0'),
            'api_url'  => setting('whatsapp.api_url', ''),
            'api_key'  => setting('whatsapp.api_key', ''),
            'instance' => setting('whatsapp.instance', ''),
        ];

        return view('whatsapp.api-settings', compact('current'));
    }

    /**
     * POST /whatsapp/api
     */
    public function saveApiSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_url'  => 'nullable|url|max:255',
            'api_key'  => 'nullable|string|max:255',
            'instance' => 'nullable|string|max:100',
        ]);

        $group = 'whatsapp';
        $this->settings->set('whatsapp.api_url',  $group, $validated['api_url'] ?? '');
        $this->settings->set('whatsapp.api_key',  $group, $validated['api_key'] ?? '');
        $this->settings->set('whatsapp.instance', $group, $validated['instance'] ?? '');

        return redirect()->route('whatsapp.api')
            ->with('success', 'Configuración de la API guardada.');
    }

    /**
     * POST /whatsapp/test-message
     */
    public function testMessage(Request $request): JsonResponse
    {
        $validated = $request->validate(['phone' => 'required|string|max:50']);

        $conn = $this->whatsApp->validateConnection();
        if (! ($conn['ok'] ?? false)) {
            return response()->json(['success' => false, 'message' => $conn['message'] ?? 'WhatsApp no está disponible.'], 422);
        }

        $recipient = $this->whatsApp->validateRecipient($validated['phone']);
        if (! ($recipient['ok'] ?? false)) {
            return response()->json(['success' => false, 'message' => $recipient['message'] ?? 'El número de teléfono no es válido.'], 422);
        }

        $formatted = $recipient['phone'];

        $result = $this->whatsApp->sendMessage(
            $formatted,
            '✅ Mensaje de prueba desde PuntoSalud. La conexión funciona correctamente.'
        );

        $ok = $result['success'] && isset($result['data']['key']['id']);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Mensaje enviado correctamente.' : 'No se pudo enviar el mensaje. Intentá nuevamente.',
        ]);
    }

    /**
     * GET /whatsapp/messages
     */
    public function messages(Request $request): View
    {
        $query = WhatsAppMessage::with(['patient', 'appointment.professional'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && in_array($request->type, ['reminder', 'creation', 'cancellation', 'receipt'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->get('search'));

            if ($term !== '') {
                $query->where(function ($q) use ($term) {
                    $q->where('phone', 'like', "%{$term}%")
                        ->orWhereHas('patient', function ($p) use ($term) {
                            $p->search($term);
                        });
                });
            }
        }

        $messages = $query->paginate(50)->withQueryString();

        $stats = [
            'total'   => WhatsAppMessage::count(),
            'sent'    => WhatsAppMessage::where('status', 'sent')->count(),
            'failed'  => WhatsAppMessage::where('status', 'failed')->count(),
            'pending' => WhatsAppMessage::where('status', 'pending')->count(),
        ];

        return view('whatsapp.messages', compact('messages', 'stats'));
    }
}
