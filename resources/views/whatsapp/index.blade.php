@extends('layouts.app')

@section('title', 'WhatsApp - Conexión - ' . config('app.name'))
@section('mobileTitle', 'WhatsApp')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="whatsappConnection()" x-init="init()">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>WhatsApp</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>Conexión</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Conexión de WhatsApp
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Conecta un número de WhatsApp escaneando el código QR
                </p>
            </div>

            <!-- Estado de conexión -->
            <div class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium"
                 :class="connected
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                    : reconnecting
                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                        : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'">
                <span class="w-2.5 h-2.5 rounded-full"
                      :class="connected ? 'bg-emerald-500' : reconnecting ? 'bg-amber-500 animate-pulse' : 'bg-red-500'"></span>
                <span x-text="connected ? 'Conectado' : reconnecting ? 'Reconectando...' : 'Desconectado'"></span>
            </div>
        </div>
    </div>

    @if (! $isConfigured)
    <!-- Aviso: sin configurar -->
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-5 flex gap-4">
        <svg class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <div>
            <p class="font-semibold text-amber-800 dark:text-amber-300">API no configurada</p>
            <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                Antes de conectar, un administrador debe configurar la URL, la clave y el nombre de instancia
                en <strong>Sistema → WhatsApp API</strong>.
            </p>
        </div>
    </div>
    @endif

    @if (session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-5 py-4 text-emerald-700 dark:text-emerald-300 text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-5 py-4 text-red-700 dark:text-red-300 text-sm">
        {{ session('error') }}
    </div>
    @endif

    <!-- Panel principal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Panel QR / Estado -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 flex flex-col items-center justify-center gap-6 min-h-80">

            <!-- Conectado -->
            <template x-if="connected">
                <div class="flex flex-col items-center gap-4 text-center">
                    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">¡WhatsApp conectado!</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">El número está activo y listo para enviar recordatorios.</p>
                    </div>

                    <!-- Botón desconectar -->
                    @if ($isConfigured)
                    <form method="POST" action="{{ route('whatsapp.disconnect') }}"
                          onsubmit="return confirm('¿Seguro que querés cerrar la sesión de WhatsApp?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Desconectar sesión
                        </button>
                    </form>
                    @endif

                    <!-- Formulario de mensaje de prueba -->
                    <div x-data="{ phone: '', sending: false, result: null }"
                         class="w-full mt-2 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 text-left">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Enviar mensaje de prueba</p>
                        <div class="flex gap-2">
                            <input x-model="phone" type="text" placeholder="Ej: 1112345678"
                                   class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                            <button @click="
                                    sending = true; result = null;
                                    fetch('{{ route('whatsapp.test-message') }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({ phone })
                                    }).then(r => r.json()).then(d => { result = d; sending = false; }).catch(() => { result = { success: false, message: 'Error de red.' }; sending = false; })"
                                    :disabled="sending || !phone.trim()"
                                    class="px-3 py-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-md disabled:opacity-50 transition-colors whitespace-nowrap">
                                <span x-show="!sending">Enviar</span>
                                <span x-show="sending">...</span>
                            </button>
                        </div>
                        <p x-show="result" x-text="result?.message"
                           :class="result?.success ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                           class="text-xs mt-2"></p>
                    </div>
                </div>
            </template>

            <!-- No conectado -->
            <template x-if="!connected">
                <div class="flex flex-col items-center gap-4 w-full">

                    <!-- Reconectando: Baileys usa credenciales guardadas -->
                    <template x-if="reconnecting">
                        <div class="flex flex-col items-center gap-3 text-center">
                            <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-amber-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Reconectando...</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Restableciendo sesión anterior</p>
                        </div>
                    </template>

                    <!-- QR cargado -->
                    <template x-if="!reconnecting && qrBase64">
                        <div class="flex flex-col items-center gap-3">
                            <img :src="'data:image/png;base64,' + qrBase64"
                                 alt="Código QR de WhatsApp"
                                 class="w-56 h-56 rounded-lg border-4 border-white shadow-lg" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                Escaneá este código con WhatsApp en tu teléfono.<br>
                                <span class="text-amber-600 dark:text-amber-400">Se actualiza automáticamente.</span>
                            </p>
                        </div>
                    </template>

                    <!-- Esperando QR -->
                    <template x-if="!reconnecting && !qrBase64 && loading">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-56 h-56 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Generando código QR...</p>
                        </div>
                    </template>

                    <!-- Error -->
                    <template x-if="!reconnecting && error">
                        <div class="flex flex-col items-center gap-3 text-center">
                            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            <p class="text-sm text-red-600 dark:text-red-400" x-text="error"></p>
                            <button @click="error = null; startPolling()"
                                    class="text-sm text-emerald-600 dark:text-emerald-400 underline">
                                Reintentar
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Panel de instrucciones -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Cómo conectar WhatsApp</h2>
            <ol class="space-y-4">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-7 h-7 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Configurá la API</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Asegurate de que la URL, clave y nombre de instancia estén configurados en <strong>Sistema → WhatsApp API</strong>.</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-7 h-7 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Abrí WhatsApp en tu teléfono</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Andá a <strong>Ajustes → Dispositivos vinculados → Vincular dispositivo</strong>.</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-7 h-7 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Escaneá el código QR</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Apuntá la cámara al código QR que aparece a la izquierda. La página se actualizará automáticamente al conectarse.</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-7 h-7 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full flex items-center justify-center text-sm font-bold">4</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">¡Listo!</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">El sistema comenzará a enviar recordatorios automáticamente según la configuración.</p>
                    </div>
                </li>
            </ol>

            <!-- Links rápidos -->
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex gap-3 flex-wrap">
                <a href="{{ route('whatsapp.settings') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                    </svg>
                    Recordatorio
                </a>
                <a href="{{ route('whatsapp.messages') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    Ver mensajes enviados
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function whatsappConnection() {
    return {
        connected: {{ $isConnected ? 'true' : 'false' }},
        qrBase64: null,
        loading: false,
        reconnecting: false,
        error: null,
        polling: null,

        init() {
            // Si venimos de una acción (flash message), verificar el estado real
            // vía AJAX en lugar de confiar en el valor PHP inicial
            const hasFlash = {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }};

            if (hasFlash) {
                this.checkStatusOnce();
            } else if (!this.connected) {
                this.startPolling();
            }
        },

        async checkStatusOnce() {
            try {
                const res = await fetch('{{ route('whatsapp.status') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.connected = data.connected;
            } catch (e) {
                this.connected = false;
            }
            if (!this.connected) {
                this.startPolling();
            }
        },

        startPolling() {
            this.loading = true;
            this.fetchQr();
            this.polling = setInterval(() => this.fetchQr(), 3000);

            // Limpiar intervalo si se cierra la pestaña
            window.addEventListener('beforeunload', () => this.stopPolling());
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },

        async fetchQr() {
            try {
                const response = await fetch('{{ route('whatsapp.qr-code') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) {
                    throw new Error('Error al obtener el QR');
                }

                const data = await response.json();

                if (data.connected) {
                    this.connected = true;
                    this.reconnecting = false;
                    this.stopPolling();
                    this.loading = false;
                    this.qrBase64 = null;
                    return;
                }

                // Estado intermedio: Baileys reconectando con credenciales guardadas
                if (data.state === 'connecting') {
                    this.reconnecting = true;
                    this.qrBase64 = null;
                    this.loading = false;
                    return;
                }

                this.reconnecting = false;
                if (data.qr) {
                    this.qrBase64 = data.qr;
                    this.loading = false;
                    this.error = null;
                }
            } catch (e) {
                this.error = 'No se pudo comunicar con la API de WhatsApp. Verificá la configuración.';
                this.loading = false;
                this.stopPolling();
            }
        }
    };
}
</script>
@endsection
