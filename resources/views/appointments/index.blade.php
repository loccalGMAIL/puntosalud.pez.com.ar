@extends('layouts.app')

@section('title', 'Turnos')
@section('mobileTitle', 'Turnos')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="appointmentsPage()">
    
    <!-- Header con estadísticas -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
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
                    <select x-model="filters.professionalId" 
                            @change="applyFilters()"
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
                        <strong x-text="filteredAppointments.length"></strong> de 
                        <strong>{{ $stats['total'] }}</strong> turnos
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
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-200/50 dark:divide-emerald-800/30">
                    <thead class="bg-emerald-50/50 dark:bg-emerald-950/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Profesional</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Duración</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Consultorio</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-emerald-200/30 dark:divide-emerald-800/30">
                        <!-- Estado vacío -->
                        <tr x-show="filteredAppointments.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center">
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
                        <template x-for="appointment in filteredAppointments" :key="appointment.id">
                            <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-950/20 transition-colors duration-200">
                                <!-- Fecha/Hora -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(appointment.appointment_date)"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="formatTime(appointment.appointment_date)"></div>
                                    </div>
                                </td>
                                
                                <!-- Paciente -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="appointment.patient.last_name + ', ' + appointment.patient.first_name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="appointment.patient.dni"></div>
                                    </div>
                                </td>
                                
                                <!-- Profesional -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="'Dr. ' + appointment.professional.first_name + ' ' + appointment.professional.last_name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="appointment.professional.specialty.name"></div>
                                    </div>
                                </td>
                                
                                <!-- Duración -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900 dark:text-white" x-text="appointment.duration + ' min'"></span>
                                </td>
                                
                                <!-- Estado -->
                                <td class="px-6 py-4">
                                    <span :class="getStatusClass(appointment.status)" 
                                          class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full gap-1" 
                                          x-text="getStatusText(appointment.status)">
                                    </span>
                                </td>
                                
                                <!-- Monto -->
                                <td class="px-6 py-4">
                                    <span x-show="appointment.final_amount || appointment.estimated_amount" 
                                          class="text-sm font-medium text-gray-900 dark:text-white" 
                                          x-text="'$' + (appointment.final_amount || appointment.estimated_amount || 0).toLocaleString()">
                                    </span>
                                    <span x-show="!appointment.final_amount && !appointment.estimated_amount" 
                                          class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                </td>
                                
                                <!-- Consultorio -->
                                <td class="px-6 py-4">
                                    <span x-show="appointment.office" 
                                          class="text-sm text-gray-900 dark:text-white" 
                                          x-text="appointment.office?.name">
                                    </span>
                                    <span x-show="!appointment.office" 
                                          class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Botón Editar -->
                                        <button @click="openEditModal(appointment)" 
                                                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Editar turno">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown de estados -->
                                        <div class="relative" x-data="{ statusDropdownOpen: false }" @click.away="statusDropdownOpen = false">
                                            <!-- Botón de estado -->
                                            <button @click="statusDropdownOpen = !statusDropdownOpen" 
                                                    class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                    title="Cambiar estado">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
        </div>
    </div>

    @include('appointments.modal')
</div>

<script>
function appointmentsPage() {
    return {
        // Data inicial
        appointments: @json($appointments),
        professionals: @json($professionals),
        patients: @json($patients),
        offices: @json($offices),
        stats: @json($stats),
        
        // Estados del modal
        modalOpen: false,
        editingAppointment: null,
        loading: false,
        
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
            this.modalOpen = true;
            console.log('Modal state:', this.modalOpen);
        },
        
        openEditModal(appointment) {
            this.editingAppointment = appointment;
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
                status: appointment.status || 'scheduled'
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
                    alert('Por favor complete el monto y método de pago.');
                    return;
                }
                if (this.form.payment_type === 'package' && (!this.form.package_sessions || !this.form.session_price || !this.form.payment_method)) {
                    alert('Por favor complete las sesiones, precio por sesión y método de pago.');
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
                    
                    // Campos opcionales que pueden estar vacíos
                    const optionalFields = ['notes', 'office_id', 'estimated_amount', 'payment_concept', 'package_sessions', 'session_price'];
                    
                    if (value !== '' && value !== null && value !== undefined) {
                        formData.append(key, value);
                    } else if (optionalFields.includes(key) || key === 'status') {
                        formData.append(key, value || '');
                    }
                    
                    // Para boolean pay_now, asegurar que se envíe correctamente
                    if (key === 'pay_now') {
                        formData.append(key, value ? '1' : '0');
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
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        this.showNotification('Errores de validación:\n' + errorMessages, 'error');
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
        
        showNotification(message, type = 'info') {
            alert(message);
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection