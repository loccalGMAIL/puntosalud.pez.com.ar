@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $dashboardData['fecha'] }}</div>
        </div>

        <!-- Métricas principales -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            
            <!-- Card 1: Consultas del día -->
            <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/20 dark:hover:shadow-emerald-900/20">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Consultas del Día</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ $dashboardData['consultasHoy']['total'] }}</p>
                        </div>
                        <div class="mt-3 flex gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['consultasHoy']['completadas'] }} completadas</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['consultasHoy']['pendientes'] }} pendientes</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                        <svg class="h-6 w-6 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l4 4L21 3" />
                        </svg>
                    </div>
                </div>
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>

            <!-- Card 2: Ingresos del día -->
            <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10 dark:hover:shadow-emerald-900/20">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ingresos del Día</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">${{ number_format($dashboardData['ingresosHoy']['total'], 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-3 space-y-1 text-xs">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Efectivo:</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($dashboardData['ingresosHoy']['efectivo'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Transferencia:</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($dashboardData['ingresosHoy']['transferencia'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Tarjeta:</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($dashboardData['ingresosHoy']['tarjeta'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                        <!-- Dollar Sign Icon -->
                        <svg class="h-6 w-6 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <!-- Decorative gradient -->
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>

            <!-- Card 3: Profesionales activos -->
            <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10 dark:hover:shadow-emerald-900/20">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Profesionales Activos</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ $dashboardData['profesionalesActivos']['total'] }}</p>
                        </div>
                        <div class="mt-3 flex gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></div>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['profesionalesActivos']['enConsulta'] }} en consulta</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['profesionalesActivos']['disponibles'] }} disponibles</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                        <!-- Heart Icon -->
                        <svg class="h-6 w-6 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733 -0.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </div>
                </div>
                <!-- Decorative gradient -->
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>
        </div>

        <!-- Área principal dividida -->
        <div class="grid gap-4 lg:grid-cols-3">
            
            <!-- Izquierda: Lista de últimas consultas (2/3 del ancho) -->
            <div class="lg:col-span-2 rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/20 p-6 shadow-sm dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <!-- Calendar Icon -->
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 3v2M18 3v2M3 18V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6" />
                        </svg>
                        Consultas de Hoy
                    </h2>
                    <button class="text-sm text-emerald-600 hover:text-emerald-800 font-medium transition-colors duration-200 dark:text-emerald-400 dark:hover:text-emerald-300">
                        Ver todas →
                    </button>
                </div>
                
                <div class="space-y-3">
                    @forelse($dashboardData['consultasDetalle'] as $consulta)
                        <div class="group flex items-center justify-between p-4 rounded-lg border bg-white/80 transition-all duration-200 hover:shadow-md dark:bg-gray-800/50 
                            @if($consulta['status'] === 'attended') border-emerald-100 hover:border-emerald-200 dark:border-emerald-800/30 dark:hover:border-emerald-700/50
                            @elseif($consulta['status'] === 'scheduled') border-blue-100 hover:border-blue-200 dark:border-blue-800/30 dark:hover:border-blue-700/50
                            @elseif($consulta['status'] === 'cancelled') border-red-100 hover:border-red-200 dark:border-red-800/30 dark:hover:border-red-700/50
                            @else border-amber-100 hover:border-amber-200 dark:border-amber-800/30 dark:hover:border-amber-700/50 @endif">
                            
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full transition-transform duration-200 group-hover:scale-110
                                    @if($consulta['status'] === 'attended') bg-emerald-100 dark:bg-emerald-900/30
                                    @elseif($consulta['status'] === 'scheduled') bg-blue-100 dark:bg-blue-900/30
                                    @elseif($consulta['status'] === 'cancelled') bg-red-100 dark:bg-red-900/30
                                    @else bg-amber-100 dark:bg-amber-900/30 @endif">
                                    
                                    @if($consulta['status'] === 'attended')
                                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($consulta['status'] === 'scheduled')
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($consulta['status'] === 'cancelled')
                                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $consulta['paciente'] }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $consulta['profesional'] }} • {{ $consulta['hora'] }}</p>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">${{ number_format($consulta['monto'], 0, ',', '.') }}</p>
                                        <div class="flex items-center gap-1">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                @if($consulta['status'] === 'attended') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                                @elseif($consulta['status'] === 'scheduled') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                                @elseif($consulta['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                                {{ $consulta['statusLabel'] }}
                                            </span>
                                            @if($consulta['isPaid'])
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                    💰 Pagado
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Botones de acción -->
                                    <div class="flex gap-1" x-data="appointmentActions({{ $consulta['id'] }}, {{ $consulta['monto'] ?? 0 }})">
                                        @if($consulta['canMarkAttended'])
                                            <button @click="markAttended()" :disabled="loading"
                                                    class="p-1.5 text-xs bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                    title="Marcar como atendido">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        @if($consulta['canMarkCompleted'])
                                            <button @click="markCompletedAndPaid()" :disabled="loading"
                                                    class="p-1.5 text-xs bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                    title="Finalizar y cobrar">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        @if($consulta['status'] === 'scheduled')
                                            <button @click="markAbsent()" :disabled="loading"
                                                    class="p-1.5 text-xs bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                    title="Marcar como ausente">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Estado vacío -->
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 3v2M18 3v2M3 18V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No hay consultas para hoy</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Derecha: Resumen de caja (1/3 del ancho) -->
            <div class="rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <!-- Dollar Sign Icon -->
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Resumen de Caja
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-emerald-100 px-2 py-1 rounded-md dark:bg-emerald-900/30">{{ $dashboardData['fecha'] }}</span>
                </div>

                <!-- Totales por profesional -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide flex items-center gap-2">
                        <!-- Heart Icon -->
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733 -0.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        Por Profesional
                    </h3>
                    
                    <div class="space-y-3">
                        @forelse($dashboardData['resumenCaja']['porProfesional'] as $profesional)
                            <div class="flex justify-between items-center p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/30">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $profesional['nombre'] }}</span>
                                <div class="text-right">
                                    <div class="font-semibold text-emerald-700 dark:text-emerald-400">${{ number_format($profesional['total'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Prof: ${{ number_format($profesional['profesional'], 0, ',', '.') }} | Clínica: ${{ number_format($profesional['clinica'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Estado vacío -->
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay ingresos registrados hoy</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Formas de pago -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">Formas de Pago</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Efectivo</span>
                            </div>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($dashboardData['resumenCaja']['formasPago']['efectivo'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Transferencia</span>
                            </div>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">${{ number_format($dashboardData['resumenCaja']['formasPago']['transferencia'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-purple-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tarjeta</span>
                            </div>
                            <span class="font-semibold text-purple-600 dark:text-purple-400">${{ number_format($dashboardData['resumenCaja']['formasPago']['tarjeta'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Total general -->
                <div class="border-t border-emerald-200 dark:border-emerald-700/50 pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Total del Día</span>
                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($dashboardData['resumenCaja']['totalGeneral'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $dashboardData['consultasHoy']['completadas'] }} consultas completadas</span>
                        <span>{{ count($dashboardData['consultasDetalle']) }} citas del día</span>
                    </div>
                </div>

                <!-- Botón de acción -->
                <button class="w-full mt-6 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                    Ver Detalle de Caja
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para finalizar y cobrar -->
    <x-payment-modal />

    <script>
    // Dashboard Management - Modern ES6+ approach
    const DashboardAPI = {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        
        async makeRequest(url, options = {}) {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                ...options
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Error en la operación');
            }
            
            return result;
        },
        
        showNotification(message, type = 'info') {
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
            alert(`${icon} ${message}`);
        },
        
        reloadPage(delay = 500) {
            setTimeout(() => location.reload(), delay);
        }
    };

    // Global modal reference
    let globalPaymentModal = null;

    // Alpine.js appointment actions component
    function appointmentActions(appointmentId, estimatedAmount = 0) {
        return {
            loading: false,
            
            async markAttended() {
                if (this.loading) return;
                this.loading = true;
                
                try {
                    await DashboardAPI.makeRequest(`/dashboard/appointments/${appointmentId}/mark-attended`, {
                        method: 'POST'
                    });
                    
                    DashboardAPI.showNotification('Turno marcado como atendido exitosamente', 'success');
                    DashboardAPI.reloadPage();
                } catch (error) {
                    DashboardAPI.showNotification(error.message, 'error');
                    this.loading = false;
                }
            },
            
            markCompletedAndPaid() {
                globalPaymentModal?.showModal(appointmentId, estimatedAmount);
            },
            
            async markAbsent() {
                if (!confirm('¿Está seguro de marcar este turno como ausente?')) return;
                if (this.loading) return;
                
                this.loading = true;
                
                try {
                    await DashboardAPI.makeRequest(`/dashboard/appointments/${appointmentId}/mark-absent`, {
                        method: 'POST'
                    });
                    
                    DashboardAPI.showNotification('Turno marcado como ausente', 'success');
                    DashboardAPI.reloadPage();
                } catch (error) {
                    DashboardAPI.showNotification(error.message, 'error');
                    this.loading = false;
                }
            }
        };
    }
    
    // Alpine.js payment modal component
    function paymentModal() {
        return {
            show: false,
            loading: false,
            currentAppointmentId: null,
            paymentForm: { final_amount: '', payment_method: '', concept: '' },
            
            init() {
                globalPaymentModal = this;
            },
            
            showModal(appointmentId, estimatedAmount = 0) {
                this.currentAppointmentId = appointmentId;
                this.paymentForm = {
                    final_amount: estimatedAmount || '',
                    payment_method: '',
                    concept: ''
                };
                this.show = true;
            },
            
            hide() {
                this.show = false;
                this.currentAppointmentId = null;
                this.loading = false;
            },
            
            async submitPayment() {
                if (this.loading) return;
                
                // Validation
                if (!this.paymentForm.final_amount || !this.paymentForm.payment_method) {
                    DashboardAPI.showNotification('Complete todos los campos requeridos', 'error');
                    return;
                }
                
                this.loading = true;
                
                try {
                    const result = await DashboardAPI.makeRequest(
                        `/dashboard/appointments/${this.currentAppointmentId}/mark-completed-paid`,
                        {
                            method: 'POST',
                            body: JSON.stringify(this.paymentForm)
                        }
                    );
                    
                    this.hide();
                    DashboardAPI.showNotification(
                        `Turno finalizado y cobrado. Recibo #${result.receipt_number}`,
                        'success'
                    );
                    DashboardAPI.reloadPage();
                } catch (error) {
                    DashboardAPI.showNotification(error.message, 'error');
                    this.loading = false;
                }
            }
        };
    }
    </script>

    @push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
        
        /* Asegurar que el modal esté por encima de todo */
        .modal-overlay {
            z-index: 10000 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        .modal-content {
            position: relative !important;
            z-index: 10001 !important;
        }
    </style>
    @endpush
@endsection