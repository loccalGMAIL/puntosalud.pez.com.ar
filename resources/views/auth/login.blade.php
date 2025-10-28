<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PuntoSalud</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-br from-emerald-50 to-green-100 min-h-screen">
    <div class="min-h-screen flex">

        <!-- Imagen del lado izquierdo -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-3/5">
            <div
                class="relative w-full bg-gradient-to-br from-emerald-600 via-green-600 to-teal-700 flex items-center justify-center overflow-hidden">
                <!-- Imagen de fondo difuminada -->
                <img src="{{ url('back_login.png') }}" class="absolute inset-0 w-full h-full object-cover"
                    style="filter: blur(4px);" alt="Background"
                    onerror="console.error('Image failed to load:', this.src); this.style.display='none';">

                <!-- Overlay para difuminar y oscurecer -->
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/60 via-green-600/50 to-teal-700/60">
                </div>

                <!-- Contenido central -->
                <div class="relative z-10 text-center text-white px-8"
                    style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                    <p class="text-white leading-relaxed max-w-md mx-auto"
                        style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                        Plataforma integral para la gestión de consultorios médicos,
                        control de citas, pacientes y profesionales de la salud.
                    </p>

                    <!-- Características destacadas -->
                    <div class="mt-12 space-y-4 text-left max-w-md mx-auto">
                        <div class="flex items-center text-white" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                            <svg class="w-5 h-5 mr-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" style="filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.8));">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Gestión completa de turnos médicos
                        </div>
                        <div class="flex items-center text-white" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                            <svg class="w-5 h-5 mr-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" style="filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.8));">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Control de pagos y facturación
                        </div>
                        <div class="flex items-center text-white" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                            <svg class="w-5 h-5 mr-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" style="filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.8));">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Reportes y estadísticas en tiempo real
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de login -->
        <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center px-4">
            <div class="w-full max-w-md">
                <div class="bg-white shadow-2xl rounded-2xl p-8">
                    <!-- Logo y título -->
                    <div class="text-center mb-8">
                        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                            <img src="{{ asset('logo.png') }}" alt="Logo Punto Salud"
                                style="max-width:200px;max-height:200px;" />
                        </div>
                        <p class="text-gray-600 mt-2">Sistema de Gestión Médica</p>
                    </div>

                    <!-- Formulario -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Correo Electrónico
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                autofocus
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                                placeholder="tu@email.com">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña
                            </label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                                placeholder="••••••••">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember"
                                    class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2">
                                <span class="ml-2 text-sm text-gray-700">Recordarme</span>
                            </label>
                        </div>

                        <!-- Submit button -->
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white py-3 px-4 rounded-lg hover:from-emerald-600 hover:to-green-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all font-medium">
                            Iniciar Sesión
                        </button>
                    </form>

                    <!-- Footer -->
                    <div class="mt-8 text-center">
                        <p class="text-xs text-gray-500">
                            PuntoSalud v{{ config('app.version', '2.2.3') }} - &copy; {{ date('Y') }}
                            - Designed by <a target="_blank" href="https://pez.com.ar">Pez</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
