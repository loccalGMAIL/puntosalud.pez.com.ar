@extends('layouts.app')

@section('title', 'Turnos - ' . config('app.name'))
@section('mobileTitle', 'Turnos')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="appointmentsPage()">
    
    <!-- Header con estadísticas -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>Turnos</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Agenda de Turnos
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Gestiona los turnos y la agenda del centro médico
                </p>
            </div>
            <button @click="openCreateModal()" 
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <!-- Plus Icon -->
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Turno
            </button>
        </div>

        <!-- Cards de estadísticas -->
        <div class="grid gap-4 md:grid-cols-5">
            <!-- Total -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">turnos totales</p>
                    </div>
                </div>
            </div>

            <!-- Programados -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Programados</dt>
                        <dd class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['scheduled'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">pendientes</p>
                    </div>
                </div>
            </div>

            <!-- Atendidos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Atendidos</dt>
                        <dd class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['attended'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">completados</p>
                    </div>
                </div>
            </div>

            <!-- Cancelados -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cancelados</dt>
                        <dd class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['cancelled'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">cancelados</p>
                    </div>
                </div>
            </div>

            <!-- Ausentes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ausentes</dt>
                        <dd class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['absent'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">no asistieron</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v5.721c0 .926-.492 1.784-1.285 2.246l-.686.343a1.125 1.125 0 01-1.462-.396l-.423-.618a1.125 1.125 0 01-.194-.682v-5.938a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Filtros y Búsqueda
                </h3>
                <button x-show="hasActiveFilters" 
                        @click="clearFilters()"
                        class="text-sm text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium">
                    Limpiar filtros
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Rango de fechas -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rango de fechas</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input x-model="filters.startDate" 
                               @change="applyFilters()"
                               type="date" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <input x-model="filters.endDate" 
                               @change="applyFilters()"
                               type="date" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <input x-model="filters.search" 
                           type="text" 
                           placeholder="Paciente, DNI..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Filtro por profesional -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profesional</label>
                    <select id="filter-professional-select"
                            x-model="filters.professionalId"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todos los profesionales</option>
                        @foreach($professionals as $professional)
                            <option value="{{ $professional->id }}">Dr. {{ $professional->first_name }} {{ $professional->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select x-model="filters.status" 
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todos los estados</option>
                        <option value="scheduled">Programado</option>
                        <option value="attended">Atendido</option>
                        <option value="cancelled">Cancelado</option>
                        <option value="absent">Ausente</option>
                    </select>
                </div>

                <!-- Contador de resultados -->
                <div class="flex items-end">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Mostrando <strong>{{ $appointments->firstItem() }}</strong> a <strong>{{ $appointments->lastItem() }}</strong> de
                        <strong>{{ $appointments->total() }}</strong> turnos
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de turnos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                </svg>
                Agenda de Turnos
            </h3>
            
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                <div x-show="filteredAppointments.length === 0" class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No se encontraron turnos</p>
                </div>
                <template x-for="appointment in filteredAppointments" :key="'mobile-'+appointment.id">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm"
                         :class="appointment.is_between_turn ? 'border-l-4 border-l-orange-500' : (appointment.duration === 0 ? 'border-l-4 border-l-red-500' : '')">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="font-semibold text-sm text-gray-900 dark:text-white" x-text="appointment.patient.last_name + ', ' + appointment.patient.first_name"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1" x-text="'#' + appointment.id"></span>
                            </div>
                            <span :class="getStatusClass(appointment.status)"
                                  class="text-xs font-medium rounded-full px-2 py-0.5"
                                  x-text="getStatusText(appointment.status)"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <div><span class="font-medium">Fecha:</span> <span x-text="formatDate(appointment.appointment_date)"></span></div>
                            <div><span class="font-medium">Hora:</span> <span x-text="formatTime(appointment.appointment_date)"></span></div>
                            <div><span class="font-medium">Prof:</span> <span x-text="'Dr. ' + appointment.professional.last_name"></span></div>
                            <div>
                                <span x-show="appointment.final_amount || appointment.estimated_amount" class="font-medium text-gray-900 dark:text-white"
                                      x-text="'$' + parseFloat(appointment.final_amount || appointment.estimated_amount || 0).toLocaleString('es-AR', {minimumFractionDigits: 2})"></span>
                                <span x-show="!appointment.final_amount && !appointment.estimated_amount">-</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-1">
                                <template x-if="appointment.duration === 0">
                                    <span class="text-xs font-bold text-red-600">URGENCIA</span>
                                </template>
                                <template x-if="appointment.is_between_turn && appointment.duration > 0">
                                    <span class="text-xs font-bold text-orange-600">ENTRETURNO</span>
                                </template>
                                <template x-if="!appointment.is_between_turn && appointment.duration > 0">
                                    <span class="text-xs text-gray-500" x-text="appointment.duration + ' min'"></span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <button @click="openEditModal(appointment)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg" title="Editar">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" class="p-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg" title="Cambiar estado">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition class="absolute right-0 z-50 mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg" style="display:none">
                                        <button @click="changeStatus(appointment, 'scheduled'); open = false" class="block w-full text-left px-3 py-2 text-xs text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30">Programado</button>
                                        <button @click="changeStatus(appointment, 'attended'); open = false" class="block w-full text-left px-3 py-2 text-xs text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30">Atendido</button>
                                        <button @click="changeStatus(appointment, 'absent'); open = false" class="block w-full text-left px-3 py-2 text-xs text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/30">Ausente</button>
                                        <button @click="changeStatus(appointment, 'cancelled'); open = false" class="block w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-b-lg">Cancelado</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-200/50 dark:divide-emerald-800/30">
                    <thead class="bg-emerald-50/50 dark:bg-emerald-950/20">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Paciente</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Profesional</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Dur.</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Consult.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-emerald-200/30 dark:divide-emerald-800/30">
                        <!-- Estado vacío -->
                        <tr x-show="filteredAppointments.length === 0">
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No se encontraron turnos</p>
                                    <button @click="clearFilters()" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Limpiar filtros
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Filas de turnos -->
                        <template x-for="(appointment, index) in filteredAppointments" :key="appointment.id">
                            <tr class="transition-colors duration-200"
                                :class="appointment.duration === 0 ? 'bg-red-50/50 dark:bg-red-900/10 hover:bg-red-100/50 dark:hover:bg-red-900/20 border-l-4 border-l-red-500' :
                                        appointment.is_between_turn ? 'bg-orange-50/50 dark:bg-orange-900/10 hover:bg-orange-100/50 dark:hover:bg-orange-900/20 border-l-4 border-l-orange-500' :
                                        'hover:bg-emerald-50/30 dark:hover:bg-emerald-950/20'">
                                <!-- Número de turno -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400" x-text="'#' + appointment.id"></span>
                                </td>

                                <!-- Fecha/Hora -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white" x-text="formatDate(appointment.appointment_date)"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatTime(appointment.appointment_date)"></div>
                                </td>

                                <!-- Paciente -->
                                <td class="px-3 py-2">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white" x-text="appointment.patient.last_name + ', ' + appointment.patient.first_name"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="appointment.patient.dni"></div>
                                </td>

                                <!-- Profesional -->
                                <td class="px-3 py-2">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white" x-text="'Dr. ' + appointment.professional.first_name + ' ' + appointment.professional.last_name"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="appointment.professional.specialty.name"></div>
                                </td>

                                <!-- Duración -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <template x-if="appointment.duration === 0">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700">
                                            🚨 URGENCIA
                                        </span>
                                    </template>
                                    <template x-if="appointment.duration > 0 && appointment.is_between_turn">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-orange-100 text-orange-800 border border-orange-300 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-700">
                                                ⏱️ ENTRETURNO
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="appointment.duration + ' min'"></span>
                                        </div>
                                    </template>
                                    <template x-if="appointment.duration > 0 && !appointment.is_between_turn">
                                        <span class="text-xs text-gray-900 dark:text-white" x-text="appointment.duration + ' min'"></span>
                                    </template>
                                </td>

                                <!-- Estado -->
                                <td class="px-3 py-2">
                                    <span :class="getStatusClass(appointment.status)"
                                          class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full gap-1"
                                          x-text="getStatusText(appointment.status)">
                                    </span>
                                </td>

                                <!-- Monto -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span x-show="appointment.final_amount || appointment.estimated_amount"
                                          class="text-xs font-medium text-gray-900 dark:text-white"
                                          x-text="'$' + parseFloat(appointment.final_amount || appointment.estimated_amount || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                    </span>
                                    <span x-show="!appointment.final_amount && !appointment.estimated_amount"
                                          class="text-xs text-gray-500 dark:text-gray-400">-</span>
                                </td>

                                <!-- Consultorio -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span x-show="appointment.office"
                                          class="text-xs text-gray-900 dark:text-white"
                                          x-text="appointment.office?.name">
                                    </span>
                                    <span x-show="!appointment.office"
                                          class="text-xs text-gray-500 dark:text-gray-400">-</span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-3 py-2 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Botón Editar -->
                                        <button @click="openEditModal(appointment)"
                                                class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                                title="Editar turno">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown de estados -->
                                        <div class="relative" x-data="{ statusDropdownOpen: false }" @click.away="statusDropdownOpen = false">
                                            <!-- Botón de estado -->
                                            <button @click="statusDropdownOpen = !statusDropdownOpen"
                                                    class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                                    title="Cambiar estado">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                            </button>

                                            <!-- Dropdown menu -->
                                            <div x-show="statusDropdownOpen" 
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 z-50 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
                                                 style="display: none;">
                                                
                                                <!-- Programado -->
                                                <button @click="changeStatus(appointment, 'scheduled'); statusDropdownOpen = false" 
                                                        :class="appointment.status === 'scheduled' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Programado
                                                    <span x-show="appointment.status === 'scheduled'" class="ml-auto">
                                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <!-- Atendido -->
                                                <button @click="changeStatus(appointment, 'attended'); statusDropdownOpen = false" 
                                                        :class="appointment.status === 'attended' ? 'bg-green-50 dark:bg-green-900/30' : ''"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors">
                                                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Atendido
                                                    <span x-show="appointment.status === 'attended'" class="ml-auto">
                                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <!-- Ausente -->
                                                <button @click="changeStatus(appointment, 'absent'); statusDropdownOpen = false" 
                                                        :class="appointment.status === 'absent' ? 'bg-orange-50 dark:bg-orange-900/30' : ''"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/30 transition-colors">
                                                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                    </svg>
                                                    Ausente
                                                    <span x-show="appointment.status === 'absent'" class="ml-auto">
                                                        <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <!-- Cancelado -->
                                                <button @click="changeStatus(appointment, 'cancelled'); statusDropdownOpen = false" 
                                                        :class="appointment.status === 'cancelled' ? 'bg-red-50 dark:bg-red-900/30' : ''"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors rounded-b-lg">
                                                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Cancelado
                                                    <span x-show="appointment.status === 'cancelled'" class="ml-auto">
                                                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-emerald-200/50 dark:border-emerald-800/30">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>

    @include('appointments.modal')
</div>

<script>
function appointmentsPage() {
    return {
        // Data inicial
        appointments: @json($appointments->items()),
        professionals: @json($professionals),
        patients: @json($patients),
        offices: @json($offices),
        stats: @json($stats),
        
        // Estados del modal
        modalOpen: false,
        editingAppointment: null,
        loading: false,
        pastTimeError: '',
        formErrors: {},
        
        init() {
            console.log('AppointmentsPage initialized');
        },
        
        // Filtros
        filters: {
            search: '',
            startDate: new Date().toISOString().split('T')[0],
            endDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            professionalId: 'all',
            status: 'all'
        },
        
        // Formulario
        form: {
            professional_id: '',
            patient_id: '',
            appointment_date: '',
            appointment_time: '',
            duration: 30,
            office_id: '',
            notes: '',
            estimated_amount: '',
            status: 'scheduled',
            is_between_turn: false,
            // Campos de pago
            pay_now: false,
            payment_type: 'single', // 'single' o 'package'
            payment_amount: '',
            payment_method: '',
            payment_concept: '',
            // Campos específicos de paquete
            package_sessions: 6,
            session_price: ''
        },
        
        // Computed
        get filteredAppointments() {
            return this.appointments.filter(appointment => {
                const searchMatch = this.filters.search === '' || 
                    (appointment.patient.first_name + ' ' + appointment.patient.last_name).toLowerCase().includes(this.filters.search.toLowerCase()) ||
                    appointment.patient.dni.toLowerCase().includes(this.filters.search.toLowerCase());

                const professionalMatch = this.filters.professionalId === 'all' || 
                    appointment.professional.id.toString() === this.filters.professionalId;

                const statusMatch = this.filters.status === 'all' || 
                    appointment.status === this.filters.status;

                const startDate = new Date(this.filters.startDate);
                const endDate = new Date(this.filters.endDate);
                const appointmentDate = new Date(appointment.appointment_date);
                const dateMatch = appointmentDate >= startDate && appointmentDate <= endDate;

                return searchMatch && professionalMatch && statusMatch && dateMatch;
            });
        },
        
        get hasActiveFilters() {
            const today = new Date().toISOString().split('T')[0];
            const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            
            return this.filters.search !== '' || 
                   this.filters.startDate !== today ||
                   this.filters.endDate !== nextWeek ||
                   this.filters.professionalId !== 'all' || 
                   this.filters.status !== 'all';
        },
        
        // Methods
        openCreateModal() {
            console.log('Opening create modal');
            this.editingAppointment = null;
            this.resetForm();
            this.clearAllErrors();
            this.modalOpen = true;
            console.log('Modal state:', this.modalOpen);
        },

        openEditModal(appointment) {
            this.editingAppointment = appointment;
            this.clearAllErrors();
            const appointmentDate = new Date(appointment.appointment_date);

            this.form = {
                professional_id: appointment.professional.id.toString(),
                patient_id: appointment.patient.id.toString(),
                appointment_date: appointmentDate.toISOString().split('T')[0],
                appointment_time: appointmentDate.toTimeString().slice(0, 5),
                duration: appointment.duration,
                office_id: appointment.office?.id.toString() || '',
                notes: appointment.notes || '',
                estimated_amount: appointment.estimated_amount || '',
                status: appointment.status || 'scheduled',
                is_between_turn: appointment.is_between_turn || false
            };
            this.modalOpen = true;
        },
        
        resetForm() {
            this.form = {
                professional_id: '',
                patient_id: '',
                appointment_date: new Date().toISOString().split('T')[0],
                appointment_time: '',
                duration: 30,
                office_id: '',
                notes: '',
                estimated_amount: '',
                status: 'scheduled',
                is_between_turn: false,
                // Resetear campos de pago
                pay_now: false,
                payment_type: 'single',
                payment_amount: '',
                payment_method: '',
                payment_concept: '',
                package_sessions: 6,
                session_price: ''
            };
        },
        
        async submitForm() {
            // Validar pago si está activado
            if (this.form.pay_now) {
                if (this.form.payment_type === 'single' && (!this.form.payment_amount || !this.form.payment_method)) {
                    window.showToast('Por favor complete el monto y método de pago.', 'warning');
                    return;
                }
                if (this.form.payment_type === 'package' && (!this.form.package_sessions || !this.form.session_price || !this.form.payment_method)) {
                    window.showToast('Por favor complete las sesiones, precio por sesión y método de pago.', 'warning');
                    return;
                }
            }
            
            this.loading = true;
            
            try {
                const url = this.editingAppointment ? 
                    `/appointments/${this.editingAppointment.id}` : 
                    '/appointments';
                const method = this.editingAppointment ? 'PUT' : 'POST';
                
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    const value = this.form[key];

                    // Campos booleanos que siempre deben enviarse
                    if (key === 'pay_now' || key === 'is_between_turn') {
                        formData.append(key, value ? '1' : '0');
                        return;
                    }

                    // Campos opcionales que pueden estar vacíos
                    const optionalFields = ['notes', 'office_id', 'estimated_amount', 'payment_concept', 'package_sessions', 'session_price'];

                    if (value !== '' && value !== null && value !== undefined) {
                        formData.append(key, value);
                    } else if (optionalFields.includes(key) || key === 'status') {
                        formData.append(key, value || '');
                    }
                });
                
                if (this.editingAppointment) {
                    formData.append('_method', 'PUT');
                }
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.modalOpen = false;
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    if (response.status === 422 && result.errors) {
                        this.setErrors(result.errors);
                        this.showNotification('Por favor corregí los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al guardar el turno', 'error');
                    }
                }
            } catch (error) {
                this.showNotification('Error al guardar el turno', 'error');
            } finally {
                this.loading = false;
            }
        },

        validateDateTime() {
            this.pastTimeError = '';
            
            if (this.form.appointment_date && this.form.appointment_time) {
                const appointmentDateTime = new Date(this.form.appointment_date + 'T' + this.form.appointment_time);
                const now = new Date();
                
                if (appointmentDateTime <= now) {
                    this.pastTimeError = 'No se pueden programar turnos en fechas y horarios pasados.';
                    return false;
                }
            }
            return true;
        },
        
        async refreshData() {
            try {
                const params = new URLSearchParams();
                if (this.filters.startDate) params.append('start_date', this.filters.startDate);
                if (this.filters.endDate) params.append('end_date', this.filters.endDate);
                if (this.filters.professionalId !== 'all') params.append('professional_id', this.filters.professionalId);
                if (this.filters.status !== 'all') params.append('status', this.filters.status);
                if (this.filters.search) params.append('search', this.filters.search);
                
                const response = await fetch('/appointments?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                this.appointments = data.appointments;
                this.stats = data.stats;
            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        },

        applyFilters() {
            this.refreshData();
        },
        
        clearFilters() {
            this.filters = {
                search: '',
                startDate: new Date().toISOString().split('T')[0],
                endDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                professionalId: 'all',
                status: 'all'
            };
            this.refreshData();
        },
        
        async changeStatus(appointment, newStatus) {
            const statusMessages = {
                scheduled: 'Programado',
                attended: 'Atendido',
                cancelled: 'Cancelado',
                absent: 'Ausente'
            };

            if (!confirm(`¿Cambiar estado del turno a "${statusMessages[newStatus]}"?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('status', newStatus);
                formData.append('_method', 'PUT');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch(`/appointments/${appointment.id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification(result.message || 'Error al actualizar el estado', 'error');
                }
            } catch (error) {
                this.showNotification('Error al actualizar el estado', 'error');
            }
        },
        
        async deleteAppointment(appointment) {
            if (!confirm('¿Estás seguro de que quieres cancelar este turno?')) return;
            
            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch(`/appointments/${appointment.id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification(result.message || 'Error al cancelar el turno', 'error');
                }
            } catch (error) {
                this.showNotification('Error al cancelar el turno', 'error');
            }
        },
        
        // Función para calcular el total del paquete
        calculatePackageTotal() {
            const sessions = parseInt(this.form.package_sessions) || 0;
            const price = parseFloat(this.form.session_price) || 0;
            this.form.payment_amount = (sessions * price).toFixed(2);
        },
        
        // Utility functions
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        },

        getStatusClass(status) {
            const classes = {
                scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                attended: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                absent: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        },

        getStatusText(status) {
            const texts = {
                scheduled: 'Programado',
                attended: 'Atendido',
                cancelled: 'Cancelado',
                absent: 'Ausente'
            };
            return texts[status] || status;
        },
        
        clearError(field) { delete this.formErrors[field]; },
        clearAllErrors() { this.formErrors = {}; },
        setErrors(errors) {
            this.formErrors = {};
            Object.keys(errors).forEach(key => {
                this.formErrors[key] = errors[key][0];
            });
        },
        hasError(field) { return !!this.formErrors[field]; },

        showNotification(message, type = 'info') {
            window.showToast(message, type);
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
[x-cloak] { display: none !important; }

/* Estilos personalizados para Select2 en modo oscuro */
.select2-container--default .select2-selection--single {
    background-color: transparent;
    border: 1px solid rgb(209 213 219);
    border-radius: 0.375rem;
    height: 38px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
}
.select2-dropdown {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.375rem;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: rgb(5 150 105);
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.375rem;
    padding: 0.5rem;
}
/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .select2-container--default .select2-selection--single {
        background-color: rgb(55 65 81);
        border-color: rgb(75 85 99);
        color: white;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: white;
    }
    .select2-dropdown {
        background-color: rgb(55 65 81);
        border-color: rgb(75 85 99);
    }
    .select2-container--default .select2-results__option {
        background-color: rgb(55 65 81);
        color: white;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: rgb(4 120 87);
    }
    .select2-search--dropdown .select2-search__field {
        background-color: rgb(55 65 81);
        border-color: rgb(75 85 99);
        color: white;
    }
}
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script defer>
// Inicializar Select2 cuando el modal se abre
document.addEventListener('DOMContentLoaded', () => {
    let professionalSelect = null;
    let patientSelect = null;
    let modalCheckInterval = null;

    // Función para verificar si el modal está abierto
    function checkModalState() {
        const modal = document.querySelector('[x-show="modalOpen"]');

        if (modal && modal.style.display !== 'none' && !modal.hasAttribute('hidden')) {
            // Modal está abierto
            if (!professionalSelect || !patientSelect) {
                setTimeout(() => {
                    initializeSelect2();
                }, 150);
            }
        } else {
            // Modal está cerrado
            destroySelect2();
        }
    }

    // Verificar el estado del modal cada 300ms
    modalCheckInterval = setInterval(checkModalState, 300);

    function initializeSelect2() {
        // Inicializar Select2 para Profesional
        if (!professionalSelect && $('#professional-select').length) {
            professionalSelect = $('#professional-select').select2({
                placeholder: 'Buscar profesional...',
                allowClear: false,
                width: '100%',
                dropdownParent: $('.bg-white.dark\\:bg-gray-800.rounded-lg.shadow-xl').first(),
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Sincronizar con Alpine.js - Actualizar directamente el modelo
            professionalSelect.on('change', function(e) {
                const selectedValue = $(this).val();
                // Buscar el componente Alpine.js y actualizar el form
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentsPage()"]'));
                if (alpineComponent) {
                    alpineComponent.form.professional_id = selectedValue;
                }
            });

            // Prevenir que el clic en el dropdown cierre el modal
            professionalSelect.on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        }

        // Inicializar Select2 para Paciente con búsqueda por DNI
        if (!patientSelect && $('#patient-select').length) {
            patientSelect = $('#patient-select').select2({
                placeholder: 'Buscar paciente por nombre o DNI...',
                allowClear: false,
                width: '100%',
                dropdownParent: $('.bg-white.dark\\:bg-gray-800.rounded-lg.shadow-xl').first(),
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todo
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // No buscar en el placeholder vacío
                    if (!data.id) {
                        return null;
                    }

                    // Convertir todo a lowercase para búsqueda case-insensitive
                    const searchTerm = params.term.toLowerCase();
                    const text = (data.text || '').toLowerCase();

                    // Obtener atributos data del elemento option
                    const $option = $(data.element);
                    const dni = ($option.attr('data-dni') || '').toLowerCase();
                    const firstName = ($option.attr('data-first-name') || '').toLowerCase();
                    const lastName = ($option.attr('data-last-name') || '').toLowerCase();

                    // Buscar en texto completo, DNI, nombre o apellido
                    if (text.indexOf(searchTerm) > -1 ||
                        dni.indexOf(searchTerm) > -1 ||
                        firstName.indexOf(searchTerm) > -1 ||
                        lastName.indexOf(searchTerm) > -1) {
                        return data;
                    }

                    return null;
                }
            });

            // Sincronizar con Alpine.js - Actualizar directamente el modelo
            patientSelect.on('change', function(e) {
                const selectedValue = $(this).val();
                // Buscar el componente Alpine.js y actualizar el form
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentsPage()"]'));
                if (alpineComponent) {
                    alpineComponent.form.patient_id = selectedValue;
                }
            });

            // Prevenir que el clic en el dropdown cierre el modal y hacer autofocus
            patientSelect.on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        }
    }

    function destroySelect2() {
        if (professionalSelect) {
            professionalSelect.select2('destroy');
            professionalSelect = null;
        }
        if (patientSelect) {
            patientSelect.select2('destroy');
            patientSelect = null;
        }
    }

    // Inicializar Select2 para el filtro de profesionales (fuera del modal)
    setTimeout(() => {
        if ($('#filter-professional-select').length) {
            const filterProfessionalSelect = $('#filter-professional-select').select2({
                placeholder: 'Buscar profesional...',
                allowClear: false,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Sincronizar con Alpine.js cuando cambia
            filterProfessionalSelect.on('change', function(e) {
                const selectedValue = $(this).val();
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentsPage()"]'));
                if (alpineComponent) {
                    alpineComponent.filters.professionalId = selectedValue;
                    alpineComponent.applyFilters();
                }
            });
        }
    }, 500);
});
</script>
@endpush
@endsection