<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('center_name', config('app.name')) }}</title>

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
                <img src="{{ center_image('login_bg', 'back_login.png') }}" class="absolute inset-0 w-full h-full object-cover"
                    style="filter: blur(4px);" alt="Background"
                    onerror="this.style.display='none';">

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
                            <img src="{{ center_image('logo', '') }}" alt="{{ setting('center_name', config('app.name')) }}"
                                style="max-width:200px;max-height:200px;" />
                        </div>
                        <p class="text-gray-600 mt-2">Sistema de Gestión Médica</p>
                    </div>

                    @if($errors->has('center_blocked'))
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-700">Sistema bloqueado</p>
                            <p class="text-xs text-red-600 mt-0.5">{{ $errors->first('center_blocked') }}</p>
                        </div>
                    </div>
                    @endif

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
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" id="password" name="password" required
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                                    placeholder="••••••••">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="show" style="display:none" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
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
                            {{ setting('center_name', config('app.name')) }} &copy; {{ date('Y') }}
                            - Designed by <a target="_blank" href="https://pez.com.ar">Pez</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
