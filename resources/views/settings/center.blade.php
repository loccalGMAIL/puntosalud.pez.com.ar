@extends('layouts.app')

@section('title', 'Configuración del Centro - ' . config('app.name'))

@section('content')
<div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

    <!-- Breadcrumb -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">Dashboard</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">Sistema</span>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">Configuración del Centro</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configuración del Centro</h1>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('alpine:initialized', () => {
                window.showToast('{{ session('success') }}', 'success');
            });
        </script>
    @endif

    @php $centerActive = ($settings['center_active'] ?? '1') === '1'; @endphp

    <!-- Panel de estado del sistema -->
    <div x-data="{ confirmBlock: false }"
         class="mb-6 rounded-xl border-2 {{ $centerActive ? 'border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800' : 'border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800' }} p-5">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @if($centerActive)
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-emerald-800 dark:text-emerald-300">Sistema habilitado</p>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400">El acceso es normal para todos los perfiles.</p>
                    </div>
                @else
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-red-800 dark:text-red-300">Sistema bloqueado</p>
                        <p class="text-sm text-red-700 dark:text-red-400">Solo el Administrador del sistema puede ingresar.</p>
                    </div>
                @endif
            </div>

            <div>
                @if($centerActive)
                    <!-- Botón para BLOQUEAR -->
                    <button type="button" @click="confirmBlock = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-300 text-red-700 bg-white hover:bg-red-50 dark:bg-gray-800 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/30 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Bloquear acceso
                    </button>
                @else
                    <!-- Botón para HABILITAR (acción directa, no necesita confirmación) -->
                    <form action="{{ route('settings.center.toggle') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Habilitar acceso
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Modal de confirmación para bloqueo -->
        <div x-show="confirmBlock" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 max-w-sm mx-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">¿Bloquear el sistema?</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Todos los usuarios (excepto el Administrador del sistema) serán desconectados y no podrán ingresar hasta que reactive el acceso.
                </p>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="confirmBlock = false"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium transition-colors">
                        Cancelar
                    </button>
                    <form action="{{ route('settings.center.toggle') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium transition-colors">
                            Sí, bloquear
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('settings.center.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Datos del Centro -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Datos del Centro</h2>

                <div class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <label for="center_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre del Centro <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="center_name" name="center_name"
                            value="{{ old('center_name', $settings['center_name'] ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-gray-700 dark:text-white @error('center_name') border-red-500 @enderror"
                            placeholder="Nombre del centro médico">
                        @error('center_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subtítulo -->
                    <div>
                        <label for="center_subtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Subtítulo
                        </label>
                        <input type="text" id="center_subtitle" name="center_subtitle"
                            value="{{ old('center_subtitle', $settings['center_subtitle'] ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Ej: Clínica de especialidades">
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label for="center_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Dirección
                        </label>
                        <input type="text" id="center_address" name="center_address"
                            value="{{ old('center_address', $settings['center_address'] ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Dirección completa">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="center_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Teléfono
                        </label>
                        <input type="text" id="center_phone" name="center_phone"
                            value="{{ old('center_phone', $settings['center_phone'] ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="(000) 000-0000">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="center_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email
                        </label>
                        <input type="email" id="center_email" name="center_email"
                            value="{{ old('center_email', $settings['center_email'] ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-gray-700 dark:text-white @error('center_email') border-red-500 @enderror"
                            placeholder="contacto@centro.com">
                        @error('center_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Imágenes -->
            <div class="space-y-6">

                <!-- Logo -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                     x-data="{ preview: null }">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo del Centro</h2>

                    <div class="flex items-start gap-4">
                        <!-- Preview actual -->
                        <div class="flex-shrink-0">
                            <img x-show="!preview"
                                src="{{ center_image('logo', 'logo.png') }}"
                                alt="Logo actual"
                                class="w-24 h-24 object-contain rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2">
                            <img x-show="preview" :src="preview"
                                alt="Vista previa"
                                class="w-24 h-24 object-contain rounded-lg border border-emerald-300 dark:border-emerald-600 bg-gray-50 dark:bg-gray-700 p-2">
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">
                                Formatos permitidos: PNG, JPG, JPEG, WEBP, SVG. Máx. 2MB.
                            </label>
                            <input type="file" id="logo" name="logo"
                                accept=".png,.jpg,.jpeg,.webp,.svg"
                                x-on:change="const f = $event.target.files[0]; if(f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); } else { preview = null; }"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400
                                    file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                    file:text-sm file:font-medium
                                    file:bg-emerald-50 file:text-emerald-700
                                    hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-400">
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Imagen de Login -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                     x-data="{ preview: null }">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Imagen de Fondo (Login)</h2>

                    @php $loginBg = center_image('login_bg', ''); @endphp

                    <div class="flex items-start gap-4">
                        <!-- Preview actual -->
                        <div class="flex-shrink-0">
                            @if($loginBg)
                                <img x-show="!preview"
                                    src="{{ $loginBg }}"
                                    alt="Imagen de login actual"
                                    class="w-24 h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                            @else
                                <div x-show="!preview"
                                    class="w-24 h-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                    <span class="text-xs text-gray-400 text-center px-1">Sin imagen</span>
                                </div>
                            @endif
                            <img x-show="preview" :src="preview"
                                alt="Vista previa"
                                class="w-24 h-24 object-cover rounded-lg border border-emerald-300 dark:border-emerald-600">
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">
                                Formatos permitidos: PNG, JPG, JPEG, WEBP. Máx. 4MB.<br>
                                Se muestra en el panel izquierdo de la pantalla de inicio de sesión.
                            </label>
                            <input type="file" id="login_bg" name="login_bg"
                                accept=".png,.jpg,.jpeg,.webp"
                                x-on:change="const f = $event.target.files[0]; if(f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); } else { preview = null; }"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400
                                    file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                    file:text-sm file:font-medium
                                    file:bg-emerald-50 file:text-emerald-700
                                    hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-400">
                            @error('login_bg')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Botón guardar -->
        <div class="mt-6 flex justify-end">
            <button type="submit"
                class="bg-emerald-600 text-white px-6 py-2.5 rounded-lg hover:bg-emerald-700 transition-colors font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                </svg>
                Guardar Configuración
            </button>
        </div>
    </form>

</div>
@endsection
