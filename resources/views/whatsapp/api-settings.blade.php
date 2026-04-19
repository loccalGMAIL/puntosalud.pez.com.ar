@extends('layouts.app')

@section('title', 'WhatsApp - Configuración API - ' . config('app.name'))
@section('mobileTitle', 'WhatsApp API')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Sistema</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>WhatsApp API</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Configuración de Evolution API
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Parámetros de conexión con el servidor de Evolution API
            </p>
        </div>
    </div>

    @if (session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-5 py-4 text-emerald-700 dark:text-emerald-300 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-5 py-4">
        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('whatsapp.api.save') }}">
        @csrf

        <!-- Estado del módulo -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Estado del módulo</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Interruptor general del módulo. Si está deshabilitado, no se envía ningún mensaje independientemente de las funciones activas.
                    </p>
                </div>
                <div x-data="{
                        enabled: {{ $current['enabled'] === '1' ? 'true' : 'false' }},
                        async toggle() {
                            this.enabled = !this.enabled;
                            await fetch('{{ route('whatsapp.feature.toggle') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                },
                                body: JSON.stringify({ key: 'enabled', value: this.enabled ? '1' : '0' }),
                            });
                        }
                    }" class="flex items-center gap-3 cursor-pointer" @click="toggle()">
                    <div class="w-11 h-6 rounded-full transition-colors duration-200 ease-in-out"
                         :class="enabled ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <div class="w-5 h-5 bg-white rounded-full shadow transform transition-transform duration-200 ease-in-out mt-0.5"
                             :class="enabled ? 'translate-x-5 ml-0.5' : 'translate-x-0.5'"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"
                          x-text="enabled ? 'Habilitado' : 'Deshabilitado'"></span>
                </div>
            </div>
        </div>

        <!-- Parámetros de conexión -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Parámetros de conexión</h2>

            <div class="space-y-5">
                <!-- URL de la API -->
                <div>
                    <label for="api_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        URL de la API
                    </label>
                    <input type="url" id="api_url" name="api_url"
                           value="{{ old('api_url', $current['api_url']) }}"
                           placeholder="http://localhost:8080"
                           class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        URL base del servidor Evolution API, sin barra al final. Ej: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">http://localhost:8080</code>
                    </p>
                </div>

                <!-- API Key -->
                <div x-data="{ showKey: false }">
                    <label for="api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        API Key
                    </label>
                    <div class="relative max-w-md">
                        <input :type="showKey ? 'text' : 'password'"
                               id="api_key" name="api_key"
                               value="{{ old('api_key', $current['api_key']) }}"
                               placeholder="Clave de API de Evolution"
                               class="w-full px-3 py-2 pr-10 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors">
                        <button type="button" @click="showKey = !showKey"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg x-show="!showKey" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="showKey" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Se configura en el archivo <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">.env</code> del servidor Evolution API como <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">AUTHENTICATION_API_KEY</code>
                    </p>
                </div>

                <!-- Nombre de instancia -->
                <div>
                    <label for="instance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nombre de instancia
                    </label>
                    <input type="text" id="instance" name="instance"
                           value="{{ old('instance', $current['instance']) }}"
                           placeholder="mi-instancia"
                           class="w-full max-w-xs px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Nombre de la instancia creada en Evolution API. Debe existir previamente en el servidor.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="{{ route('whatsapp.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Ir a Conexión
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Guardar
            </button>
        </div>
    </form>

</div>
@endsection
