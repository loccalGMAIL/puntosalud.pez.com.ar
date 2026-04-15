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
        $state       = $this->whatsApp->connectionState();
        $isConnected = ($state['state'] ?? '') === 'open';
        $isConfigured = $this->whatsApp->isConfigured();

        return view('whatsapp.index', compact('state', 'isConnected', 'isConfigured'));
    }

    /**
     * GET /whatsapp/qr-code — Polling Ajax: retorna QR o estado conectado
     */
    public function qrCode(): JsonResponse
    {
        $qrData    = $this->whatsApp->getQrCode();
        $connState = $this->whatsApp->connectionState();
        $connected = ($connState['state'] ?? '') === 'open';

        return response()->json([
            'connected' => $connected,
            'qr'        => $qrData['base64'] ?? null,
            'state'     => $connState['state'] ?? 'unknown',
        ]);
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
        $success = $this->whatsApp->disconnect();

        return redirect()->route('whatsapp.index')
            ->with(
                $success ? 'success' : 'error',
                $success ? 'Sesión de WhatsApp cerrada correctamente.' : 'No se pudo cerrar la sesión de WhatsApp.'
            );
    }

    /**
     * GET /whatsapp/settings — Configuración del recordatorio (mensaje y timing)
     */
    public function settings(): View
    {
        $current = [
            'hours_before' => setting('whatsapp.hours_before', '24'),
            'template'     => setting('whatsapp.template', ''),
        ];

        return view('whatsapp.settings', compact('current'));
    }

    /**
     * POST /whatsapp/settings
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hours_before' => 'required|in:1,2,4,12,24,48',
            'template'     => 'required|string|max:1000',
        ]);

        $group = 'whatsapp';
        $this->settings->set('whatsapp.hours_before', $group, $validated['hours_before']);
        $this->settings->set('whatsapp.template',     $group, $validated['template']);

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
        $this->settings->set('whatsapp.enabled',  $group, $request->boolean('enabled') ? '1' : '0');
        $this->settings->set('whatsapp.api_url',  $group, $validated['api_url'] ?? '');
        $this->settings->set('whatsapp.api_key',  $group, $validated['api_key'] ?? '');
        $this->settings->set('whatsapp.instance', $group, $validated['instance'] ?? '');

        return redirect()->route('whatsapp.api')
            ->with('success', 'Configuración de la API guardada.');
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
