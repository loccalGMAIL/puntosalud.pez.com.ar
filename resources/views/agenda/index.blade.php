@extends('layouts.app')

@section('title', 'Agenda - ' . config('app.name'))
@section('mobileTitle', 'Agenda')

@section('content')
<div class="p-6" x-data="appointmentModal()" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Agenda</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Agenda</h1>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Professional Selector -->
            <form method="GET" action="{{ route('agenda.index') }}" class="flex gap-2 flex-1" id="professional-form">
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <select name="professional_id"
                        id="agenda-professional-select"
                        style="width: 500px;"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Seleccionar profesional</option>
                    @foreach($professionals as $professional)
                        <option value="{{ $professional->id }}"
                                data-specialty="{{ $professional->specialty->name }}"
                                {{ $selectedProfessional == $professional->id ? 'selected' : '' }}>
                            Dr. {{ $professional->full_name }} - {{ $professional->specialty->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Month Navigation -->
            <div class="flex items-center gap-2">
                <a href="{{ route('agenda.index', ['month' => $date->copy()->subMonth()->format('Y-m'), 'professional_id' => $selectedProfessional]) }}" 
                   class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
                
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[180px] text-center">
                    {{ $date->locale('es')->isoFormat('MMMM YYYY') }}
                </h2>
                
                <a href="{{ route('agenda.index', ['month' => $date->copy()->addMonth()->format('Y-m'), 'professional_id' => $selectedProfessional]) }}" 
                   class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Alerta de Estado de Caja -->
    @if($cashStatus['is_closed'])
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v7.5a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Caja Cerrada</h3>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                        La caja del día está cerrada. No se pueden crear turnos para hoy ni procesar pagos inmediatos.
                        <a href="{{ route('cash.daily') }}" class="underline hover:no-underline">Ir a Caja</a>
                    </p>
                </div>
            </div>
        </div>
    @elseif($cashStatus['needs_opening'])
        <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Caja Sin Abrir</h3>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                        La caja del día no ha sido abierta. Debe abrir la caja antes de crear turnos para hoy o procesar pagos.
                        <a href="{{ route('cash.daily') }}" class="underline hover:no-underline">Abrir Caja</a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if(!$selectedProfessional)
        <!-- No Professional Selected - Show Top Professionals -->
        <div class="space-y-6">
            <!-- Header -->
            {{-- <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-950/20 dark:to-blue-950/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 mx-auto mb-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Selecciona un profesional</h3>
                <p class="text-gray-600 dark:text-gray-400">Elige un profesional del selector superior o selecciona uno de los más frecuentes</p>
            </div> --}}

            <!-- Top Professionals Cards -->
            @if($topProfessionals->count() > 0)
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                        Profesionales más frecuentes
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($topProfessionals as $professional)
                            <a href="{{ route('agenda.index', ['professional_id' => $professional->id, 'month' => $currentMonth]) }}"
                               class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-lg transition-all duration-200 overflow-hidden">
                                <!-- Header con gradiente -->
                                <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-950/30 dark:to-blue-950/30 p-4 border-b border-gray-200 dark:border-gray-700 group-hover:from-emerald-100 group-hover:to-blue-100 dark:group-hover:from-emerald-900/40 dark:group-hover:to-blue-900/40 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar con iniciales -->
                                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                            {{ strtoupper(substr($professional->first_name, 0, 1) . substr($professional->last_name, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h5 class="font-semibold text-gray-900 dark:text-white truncate">
                                                Dr. {{ $professional->full_name }}
                                            </h5>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                {{ $professional->specialty->name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Body con estadísticas -->
                                <div class="p-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                                            </svg>
                                            <span>{{ $professional->appointments_count }} turnos</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium group-hover:gap-2 transition-all">
                                            <span>Ver agenda</span>
                                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- Calendar Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Calendar Header -->
            <div class="grid grid-cols-5 bg-gray-50 dark:bg-gray-700">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie'] as $dayName)
                    <div class="p-4 text-center font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600 last:border-r-0">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-5">
                @php
                    $currentDay = $startOfCalendar->copy();
                @endphp
                
                @while($currentDay->lte($endOfCalendar))
                    @php
                        $dayOfWeek = $currentDay->dayOfWeek === 0 ? 7 : $currentDay->dayOfWeek; // Convert Sunday from 0 to 7

                        // Skip Saturday (6) and Sunday (7)
                        if ($dayOfWeek === 6 || $dayOfWeek === 7) {
                            $currentDay->addDay();
                            continue;
                        }

                        $dayKey = $currentDay->format('Y-m-d');
                        $dayAppointments = $appointments[$dayKey] ?? collect();
                        $isCurrentMonth = $currentDay->month === $date->month;
                        $isToday = $currentDay->isToday();
                        $hasSchedule = $professionalSchedules->has($dayOfWeek);
                        $isPast = $currentDay->isBefore(today());

                        // Verificar si es feriado activo
                        $isHoliday = $holidays->has($dayKey);
                        $holidayData = $isHoliday ? $holidays->get($dayKey) : null;
                    @endphp
                    
                    <div class="min-h-[120px] p-2 border-r border-b border-gray-200 dark:border-gray-600 last:border-r-0
                                {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-900' :
                                   ($isHoliday ? 'bg-red-50/70 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800' :
                                   (!$hasSchedule ? 'bg-gray-300 dark:bg-gray-600' : 'bg-white dark:bg-gray-800')) }}">

                        <!-- Day Number and Add Button -->
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1">
                                <span class="text-sm font-medium
                                            {{ !$isCurrentMonth ? 'text-gray-400 dark:text-gray-600' :
                                               ($isHoliday ? 'text-red-700 dark:text-red-400' :
                                               (!$hasSchedule ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white')) }}
                                            {{ $isToday ? 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs' : '' }}">
                                    {{ $currentDay->day }}
                                </span>
                                @if($isHoliday)
                                    <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" title="{{ $holidayData->reason }}">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Add Button (only for enabled days and not past days and not holidays) -->
                            @if($hasSchedule && $isCurrentMonth && !$isPast && !$isHoliday)
                                <button onclick="openAppointmentModal('{{ $currentDay->format('Y-m-d') }}', {{ $selectedProfessional }})"
                                        class="flex items-center justify-center w-5 h-5 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                        title="Agregar turno">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Holiday Label -->
                        @if($isHoliday)
                            <div class="mb-2 px-2 py-1 bg-red-100 dark:bg-red-900/40 rounded text-xs text-red-800 dark:text-red-300 font-medium truncate" title="{{ $holidayData->reason }}">
                                {{ $holidayData->reason }}
                            </div>
                        @endif

                        <!-- Appointments -->
                        @if($hasSchedule && $dayAppointments->count() > 0)
                            <div class="space-y-1 overflow-hidden">
                                @php
                                    $maxVisible = 2; // Reducido para dejar espacio al botón
                                    $visibleAppointments = $dayAppointments->take($maxVisible);
                                    $remainingCount = $dayAppointments->count() - $maxVisible;
                                @endphp
                                
                                @foreach($visibleAppointments as $appointment)
                                    @php
                                        // Check if appointment is urgency (duration = 0)
                                        $isUrgency = $appointment->is_urgency;

                                        if ($isUrgency) {
                                            $statusColor = 'bg-red-100 text-red-800 border-2 border-red-400 dark:bg-red-900/40 dark:text-red-300 dark:border-red-600 hover:bg-red-200 dark:hover:bg-red-900/60 font-bold';
                                        } else {
                                            $statusColors = [
                                                'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50',
                                                'attended' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50',
                                                'absent' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50',
                                            ];
                                            $statusColor = $statusColors[$appointment->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600';
                                        }

                                        $appointmentIsPast = $appointment->appointment_date->isPast();
                                    @endphp
                                    
                                    @if($appointmentIsPast)
                                        <div class="w-full text-left text-xs rounded px-2 py-1 {{ $statusColor }} truncate opacity-75"
                                             title="Turno pasado - No editable">
                                            <div class="font-medium">{{ $appointment->appointment_date->format('H:i') }}</div>
                                            <div class="truncate">{{ $appointment->patient->full_name }}</div>
                                        </div>
                                    @else
                                        <button onclick="openEditAppointmentModal({{ $appointment->id }})" 
                                                class="w-full text-left text-xs rounded px-2 py-1 {{ $statusColor }} truncate cursor-pointer transition-colors"
                                                title="Editar turno">
                                            <div class="font-medium">{{ $appointment->appointment_date->format('H:i') }}</div>
                                            <div class="truncate">{{ $appointment->patient->full_name }}</div>
                                        </button>
                                    @endif
                                @endforeach
                                
                                @if($remainingCount > 0)
                                    <button onclick="openDayModal('{{ $currentDay->format('Y-m-d') }}', {{ $selectedProfessional }})"
                                            class="w-full text-xs text-gray-500 dark:text-gray-400 text-center py-1 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer transition-colors"
                                            title="Ver todos los turnos del día">
                                        +{{ $remainingCount }} más
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    @php
                        $currentDay->addDay();
                    @endphp
                @endwhile
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-blue-100 dark:bg-blue-900/30 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Programado</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-100 dark:bg-green-900/30 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Atendido</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-100 dark:bg-red-900/30 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Ausente</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Feriado</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-300 dark:bg-gray-600 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Día sin atención</span>
            </div>
        </div>
    @endif

    <!-- Include Appointment Modal -->
    @include('appointments.modal')

    <!-- Day Details Modal -->
    <div x-show="dayModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
        
        <!-- Modal Content -->
        <div @click.away="dayModalOpen = false" 
             class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                        </svg>
                        <span x-text="'Turnos del ' + formatDateSpanish(selectedDayDate)"></span>
                    </h3>
                    <button @click="dayModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="'Profesional: Dr. ' + selectedProfessionalName"></p>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Add New Appointment Button -->
                <div class="mb-4" x-show="!isDayInPast()">
                    <button @click="openCreateModal(selectedDayDate, selectedProfessionalId); dayModalOpen = false;"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nuevo Turno
                    </button>
                </div>

                <!-- Past Day Notice -->
                <div class="mb-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3" x-show="isDayInPast()">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Día pasado - Solo visualización. Los turnos atendidos no se pueden editar.</span>
                    </div>
                </div>

                <!-- Appointments List -->
                <div class="space-y-3" x-show="dayAppointments.length > 0">
                    <template x-for="appointment in dayAppointments" :key="appointment.id">
                        <div class="rounded-lg p-4 transition-colors"
                             :class="appointment.duration === 0 ?
                                'border-2 border-red-400 dark:border-red-600 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30' :
                                appointment.is_between_turn ?
                                'border-2 border-orange-400 dark:border-orange-600 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/30' :
                                'border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="font-medium text-gray-900 dark:text-white" x-text="formatTime(appointment.appointment_date)"></span>
                                        <template x-if="appointment.duration === 0">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700">
                                                🚨 URGENCIA
                                            </span>
                                        </template>
                                        <template x-if="appointment.duration > 0 && appointment.is_between_turn">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-orange-100 text-orange-800 border border-orange-300 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-700">
                                                ⏱️ ENTRETURNO
                                            </span>
                                        </template>
                                        <span :class="getStatusBadgeClass(appointment.status)"
                                              class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
                                              x-text="getStatusText(appointment.status)">
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="appointment.patient.first_name + ' ' + appointment.patient.last_name"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="'DNI: ' + appointment.patient.dni"></p>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="'Duración: ' + appointment.duration + ' min'"></span>
                                        <span x-show="appointment.estimated_amount" x-text="' • Monto: $' + appointment.estimated_amount"></span>
                                        <span x-show="appointment.office" x-text="' • Consultorio: ' + appointment.office.name"></span>
                                    </div>
                                    <div x-show="appointment.notes" class="mt-2 text-xs text-gray-600 dark:text-gray-300" x-text="'Notas: ' + appointment.notes"></div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <!-- Edit button - disabled for attended appointments or past dates -->
                                    <button @click="openEditModal(appointment); dayModalOpen = false;"
                                            :disabled="appointment.status === 'attended' || isAppointmentInPast(appointment)"
                                            :class="appointment.status === 'attended' || isAppointmentInPast(appointment) ?
                                                'p-2 text-gray-400 dark:text-gray-600 rounded-lg cursor-not-allowed opacity-50' :
                                                'p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors'"
                                            :title="appointment.status === 'attended' ? 'Turno atendido - No editable' : 'Editar turno'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Empty State -->
                <div x-show="dayAppointments.length === 0" class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay turnos</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No hay turnos programados para este día.</p>
                    <button @click="openCreateModal(selectedDayDate, selectedProfessionalId); dayModalOpen = false;" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Agregar Primer Turno
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end border-t border-gray-200 dark:border-gray-600">
                <button @click="dayModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openAppointmentModal(date, professionalId) {
    // Dispatch event to Alpine.js component
    document.dispatchEvent(new CustomEvent('open-appointment-modal', {
        detail: {
            date: date,
            professionalId: professionalId
        }
    }));
}

function openEditAppointmentModal(appointmentId) {
    // Dispatch event to Alpine.js component
    document.dispatchEvent(new CustomEvent('open-edit-appointment-modal', {
        detail: {
            appointmentId: appointmentId
        }
    }));
}

function openDayModal(date, professionalId) {
    // Dispatch event to Alpine.js component
    document.dispatchEvent(new CustomEvent('open-day-modal', {
        detail: {
            date: date,
            professionalId: professionalId
        }
    }));
}

// Initialize Alpine.js component for the modal
document.addEventListener('alpine:init', () => {
    Alpine.data('appointmentModal', () => ({
        modalOpen: false,
        dayModalOpen: false,
        editingAppointment: null,
        loading: false,
        professionals: @json($professionals),
        patients: @json($patients),
        offices: @json($offices),
        allAppointments: @json($appointments),
        
        // Error state for past datetime validation
        pastTimeError: '',
        
        // Day modal data
        selectedDayDate: '',
        selectedProfessionalId: null,
        selectedProfessionalName: '',
        dayAppointments: [],
        
        form: {
            professional_id: '',
            patient_id: '',
            appointment_date: '',
            appointment_time: '',
            duration: 30,
            office_id: '',
            notes: '',
            estimated_amount: '',
            status: 'scheduled'
        },

        init() {
            // Listen for modal open events
            document.addEventListener('open-appointment-modal', (event) => {
                this.openCreateModal(event.detail.date, event.detail.professionalId);
            });
            
            document.addEventListener('open-edit-appointment-modal', (event) => {
                this.openEditModalById(event.detail.appointmentId);
            });
            
            document.addEventListener('open-day-modal', (event) => {
                this.openDayModal(event.detail.date, event.detail.professionalId);
            });
        },

        openCreateModal(date = null, professionalId = null) {
            this.editingAppointment = null;
            this.resetForm();
            
            if (date) {
                this.form.appointment_date = date;
            }
            if (professionalId) {
                this.form.professional_id = professionalId.toString();
            }
            
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
                status: 'scheduled'
            };
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

        async submitForm() {
            // Validate datetime before submitting
            if (!this.validateDateTime()) {
                return;
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
                    if (value !== '' && value !== null && value !== undefined) {
                        formData.append(key, value);
                    } else if (key === 'status' || key === 'notes' || key === 'office_id' || key === 'estimated_amount') {
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
                    this.showNotification(result.message, 'success');
                    // Reload page to show changes
                    setTimeout(() => window.location.reload(), 1000);
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

        openDayModal(date, professionalId) {
            this.selectedDayDate = date;
            this.selectedProfessionalId = professionalId;
            
            // Find professional name
            const professional = this.professionals.find(p => p.id == professionalId);
            this.selectedProfessionalName = professional ? professional.first_name + ' ' + professional.last_name : '';
            
            // Get appointments for this day
            this.dayAppointments = this.allAppointments[date] || [];
            
            this.dayModalOpen = true;
        },

        openEditModalById(appointmentId) {
            // Find appointment in all appointments data
            let appointment = null;
            Object.values(this.allAppointments).forEach(dayApps => {
                if (Array.isArray(dayApps)) {
                    const found = dayApps.find(app => app.id == appointmentId);
                    if (found) {
                        appointment = found;
                    }
                }
            });
            
            if (appointment) {
                this.openEditModal(appointment);
            }
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

        formatDateSpanish(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
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

        getStatusBadgeClass(status) {
            const classes = {
                scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                attended: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
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

        isDayInPast() {
            if (!this.selectedDayDate) return false;
            const selectedDate = new Date(this.selectedDayDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            return selectedDate < today;
        },

        isAppointmentInPast(appointment) {
            if (!appointment || !appointment.appointment_date) return false;
            const appointmentDate = new Date(appointment.appointment_date);
            const now = new Date();
            return appointmentDate < now;
        },

        showNotification(message, type = 'info') {
            alert(message);
        }
    }))
});
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
[x-cloak] { display: none !important; }

/* Estilos personalizados para Select2 */
.select2-container--default .select2-selection--single {
    background-color: transparent;
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
    height: 42px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 42px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
}
.select2-dropdown {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: rgb(37 99 235);
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
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
        background-color: rgb(29 78 216);
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
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar Select2 para el selector de profesionales en la agenda
    if ($('#agenda-professional-select').length) {
        const agendaProfessionalSelect = $('#agenda-professional-select').select2({
            placeholder: 'Buscar profesional...',
            allowClear: true,
            width: '500px',
            dropdownAutoWidth: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Submit form cuando cambia la selección
        agendaProfessionalSelect.on('change', function() {
            document.getElementById('professional-form').submit();
        });

        // Autofocus en el campo de búsqueda
        agendaProfessionalSelect.on('select2:open', function() {
            document.querySelector('.select2-search__field').focus();
        });
    }

    // Variables para los selects del modal
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
        // Inicializar Select2 para Profesional en el modal
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
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentModal()"]'));
                if (alpineComponent) {
                    alpineComponent.form.professional_id = selectedValue;
                }
            });

            // Autofocus en el campo de búsqueda
            professionalSelect.on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        }

        // Inicializar Select2 para Paciente con búsqueda por DNI en el modal
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
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentModal()"]'));
                if (alpineComponent) {
                    alpineComponent.form.patient_id = selectedValue;
                }
            });

            // Autofocus en el campo de búsqueda
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
});
</script>
@endpush
@endsection