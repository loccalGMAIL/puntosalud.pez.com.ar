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
            <div class="grid grid-cols-6 bg-gray-50 dark:bg-gray-700">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $dayName)
                    <div class="p-4 text-center font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600 last:border-r-0">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-6">
                @php
                    $currentDay = $startOfCalendar->copy();
                @endphp
                
                @while($currentDay->lte($endOfCalendar))
                    @php
                        $dayOfWeek = $currentDay->dayOfWeek === 0 ? 7 : $currentDay->dayOfWeek; // Convert Sunday from 0 to 7

                        // Skip Sunday (7)
                        if ($dayOfWeek === 7) {
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

                        // Verificar si hay cumpleaños
                        $hasBirthdays = $birthdays->has($dayKey);
                        $birthdayProfessionals = $hasBirthdays ? $birthdays->get($dayKey) : collect();
                        $birthdaysText = $birthdayProfessionals->map(fn($p) => "Dr. {$p['name']} ({$p['age']} años)")->join(', ');
                    @endphp
                    
                    <div class="min-h-[120px] p-2 border-r border-b border-gray-200 dark:border-gray-600 last:border-r-0
                                {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-900' :
                                   ($isHoliday ? 'bg-red-50/70 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800' :
                                   (!$hasSchedule ? 'bg-gray-300 dark:bg-gray-600' : 'bg-white dark:bg-gray-800')) }}
                                {{ ($hasSchedule && $isCurrentMonth && !$isHoliday) ? 'cursor-pointer hover:brightness-95' : '' }}"
                         @if($hasSchedule && $isCurrentMonth && !$isHoliday)
                             onclick="openDayModal('{{ $currentDay->format('Y-m-d') }}', {{ $selectedProfessional }})"
                         @endif>

                        <!-- Day Number -->
                        <div class="flex items-center gap-1 mb-2">
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
                            @if($hasBirthdays)
                                <span class="text-base cursor-help" title="🎉 Cumpleaños: {{ $birthdaysText }}" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));">🎂</span>
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
                                    $maxVisible = 3;
                                    $visibleAppointments = $dayAppointments->take($maxVisible);
                                @endphp

                                @foreach($visibleAppointments as $appointment)
                                    @php
                                        $isUrgency = $appointment->is_urgency;
                                        if ($isUrgency) {
                                            $statusColor = 'bg-red-100 text-red-800 border-2 border-red-400 dark:bg-red-900/40 dark:text-red-300 dark:border-red-600 font-bold';
                                        } else {
                                            $statusColors = [
                                                'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                'attended' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'absent' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            ];
                                            $statusColor = $statusColors[$appointment->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                        }
                                        $appointmentIsPast = $appointment->appointment_date->isPast();
                                    @endphp
                                    <div class="w-full text-xs rounded px-2 py-1 {{ $statusColor }} truncate {{ $appointmentIsPast ? 'opacity-75' : '' }}">
                                        <div class="font-medium">{{ $appointment->appointment_date->format('H:i') }}</div>
                                        <div class="truncate">{{ $appointment->patient->full_name }}</div>
                                    </div>
                                @endforeach
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

    <!-- Include Patient Modal -->
    <div x-data="patientModal()">
        @include('patients.modal')
    </div>

    <!-- Day Details Modal -->
    <div x-show="dayModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
        
        <!-- Modal Content -->
        <div @click.away="dayModalOpen = false" 
             class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            
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
                <!-- Barra superior: acción + info de horario -->
                <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <button x-show="!isDayInPast()"
                                @click="openCreateModal(selectedDayDate, selectedProfessionalId); dayModalOpen = false;"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nuevo Turno
                        </button>
                        <div x-show="isDayInPast()" class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            Solo visualización
                        </div>
                    </div>
                    <div x-show="daySchedule" class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jornada: <span class="font-medium" x-text="daySchedule ? daySchedule.startTime + ' – ' + daySchedule.endTime : ''"></span>
                    </div>
                </div>

                <!-- Leyenda -->
                <div x-show="daySchedule" class="flex flex-wrap gap-x-4 gap-y-1 mb-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span> Programado</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Atendido</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span> Ausente</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Urgencia</span>
                    <span x-show="!isDayInPast()" class="flex items-center gap-1"><span class="w-3 h-2 rounded border border-dashed border-emerald-500 inline-block bg-emerald-50 dark:bg-emerald-900/20"></span> Disponible</span>
                </div>

                <!-- Timeline a ancho completo -->
                <div x-show="daySchedule" class="overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700"
                     style="max-height: 65vh">
                    <div class="relative bg-white dark:bg-gray-900" :style="`height: ${timelineHeightPx}px; min-height: 80px`">

                        <!-- Fondo de filas alternadas por hora (visual guide) -->
                        <template x-for="hour in timelineHours" :key="'bg-' + hour.label">
                            <div class="absolute left-0 right-0 pointer-events-none"
                                 :style="`top: ${hour.topPx}px; height: ${60 * pxPerMin}px`"
                                 :class="timelineHours.indexOf(hour) % 2 === 0
                                     ? 'bg-white dark:bg-gray-900'
                                     : 'bg-gray-50/60 dark:bg-gray-800/40'">
                            </div>
                        </template>

                        <!-- Marcas de hora completa — z-20 -->
                        <template x-for="hour in timelineHours" :key="hour.label">
                            <div class="absolute left-0 right-0 flex items-center pointer-events-none z-20"
                                 :style="`top: ${hour.topPx}px`">
                                <div class="w-14 flex-shrink-0 pr-2 text-right text-[11px] font-medium text-gray-400 dark:text-gray-500 leading-none select-none -translate-y-1/2"
                                     x-text="hour.label"></div>
                                <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
                            </div>
                        </template>

                        <!-- Marcas de media hora — z-10 -->
                        <template x-for="(half, idx) in timelineHalfHours" :key="idx">
                            <div class="absolute left-14 right-0 border-t border-dashed border-gray-100 dark:border-gray-800 pointer-events-none z-10"
                                 :style="`top: ${half.topPx}px`"></div>
                        </template>

                        <!-- Slots libres — z-0 -->
                        <template x-for="item in timelineLayout.items.filter(i => i.type === 'free')" :key="item.startMins">
                            <button class="absolute left-[58px] right-2 rounded-sm
                                           border border-dashed border-emerald-300 dark:border-emerald-700
                                           bg-emerald-50/50 dark:bg-emerald-900/10
                                           hover:bg-emerald-100/80 dark:hover:bg-emerald-800/30
                                           hover:border-emerald-400 dark:hover:border-emerald-600
                                           flex items-center justify-center gap-1
                                           text-[11px] text-emerald-600 dark:text-emerald-500
                                           transition-colors group z-0"
                                    :style="`top: ${item.topPx + 1}px; height: ${item.heightPx}px`"
                                    @click="openCreateModalWithTime(selectedDayDate, selectedProfessionalId, item.label); dayModalOpen = false;">
                                <svg class="w-3 h-3 opacity-50 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span class="opacity-70 group-hover:opacity-100 transition-opacity" x-text="item.label"></span>
                            </button>
                        </template>

                        <!-- Bloques de turnos — z-10 -->
                        <template x-for="item in timelineLayout.items.filter(i => i.type === 'appointment')" :key="item.apt.id">
                            <div class="absolute left-[58px] right-2 rounded-sm overflow-hidden transition-all z-10 select-none"
                                 :class="[appointmentBlockClass(item.apt), isAppointmentInPast(item.apt) ? 'opacity-55 cursor-default' : 'cursor-pointer hover:brightness-110 hover:shadow-md']"
                                 :style="`top: ${item.topPx + 1}px; height: ${item.heightPx - 2}px`"
                                 @click="!isAppointmentInPast(item.apt) && (openEditModal(item.apt), dayModalOpen = false)"
                                 :title="isAppointmentInPast(item.apt) ? '(Solo lectura) ' + formatTime(item.apt.appointment_date) + ' – ' + item.apt.patient.last_name + ', ' + item.apt.patient.first_name : formatTime(item.apt.appointment_date) + ' – ' + item.apt.patient.last_name + ', ' + item.apt.patient.first_name">
                                <div class="px-2 h-full flex items-center gap-2 overflow-hidden">
                                    <span class="text-[11px] font-bold whitespace-nowrap tabular-nums" x-text="formatTime(item.apt.appointment_date)"></span>
                                    <span class="opacity-40 select-none text-[10px]">|</span>
                                    <span class="flex-1 truncate text-[11px]" x-text="item.apt.patient.last_name + ', ' + item.apt.patient.first_name"></span>
                                    <span class="whitespace-nowrap opacity-70 text-[10px] tabular-nums" x-text="item.apt.duration > 0 ? item.apt.duration + ' min' : '🚨'"></span>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

                <!-- Fallback: sin horario configurado -->
                <div x-show="!daySchedule" class="text-center py-10">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay horario configurado para este día.</p>
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

@php
$schedulesForJs = [];
if (isset($professionalSchedules)) {
    foreach ($professionalSchedules as $dayOfWeek => $schedule) {
        $schedulesForJs[$dayOfWeek] = [
            'start_time' => \Carbon\Carbon::parse($schedule->getRawOriginal('start_time'))->format('H:i'),
            'end_time'   => \Carbon\Carbon::parse($schedule->getRawOriginal('end_time'))->format('H:i'),
        ];
    }
}
@endphp
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
        formErrors: {},
        professionals: @json($professionals),
        patients: @json($patients),
        offices: @json($offices),
        allAppointments: @json($appointments),
        schedules: @json($schedulesForJs ?? []),
        daySchedule: null,
        pxPerMin: 3,
        durationOptions: [
            { value: 5,   label: '5 minutos' },
            { value: 10,  label: '10 minutos' },
            { value: 15,  label: '15 minutos' },
            { value: 20,  label: '20 minutos' },
            { value: 25,  label: '25 minutos' },
            { value: 30,  label: '30 minutos' },
            { value: 40,  label: '40 minutos' },
            { value: 45,  label: '45 minutos' },
            { value: 60,  label: '1 hora' },
            { value: 90,  label: '1 hora 30 minutos' },
            { value: 120, label: '2 horas' },
        ],

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
            this.clearAllErrors();
            
            if (date) {
                this.form.appointment_date = date;
            }
            if (professionalId) {
                this.form.professional_id = professionalId.toString();
            }
            
            this.modalOpen = true;
        },

        openCreateModalWithTime(date, professionalId, time) {
            this.editingAppointment = null;
            this.resetForm();
            this.clearAllErrors();
            if (date) this.form.appointment_date = date;
            if (professionalId) this.form.professional_id = professionalId.toString();
            if (time) this.form.appointment_time = time;
            this.modalOpen = true;
        },

        resetForm() {
            this.form = {
                professional_id: '',
                patient_id: '',
                appointment_date: this.getTodayDate(),
                appointment_time: '',
                duration: 30,
                office_id: '',
                notes: '',
                estimated_amount: '',
                status: 'scheduled',
                is_between_turn: false,
                // Campos de pago
                pay_now: false,
                payment_type: 'single',
                payment_amount: '',
                payment_method: '',
                payment_concept: '',
                // Campos de paquete
                package_sessions: '',
                session_price: ''
            };
        },

        getTodayDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        // Retorna { maxMins, time } con el límite para no superponer al siguiente turno
        // del mismo profesional ese día, o null si no hay restricción.
        get nextAppointmentConstraint() {
            if (!this.form.appointment_date || !this.form.appointment_time || !this.form.professional_id) return null;
            const dayApps = this.allAppointments[this.form.appointment_date] || [];
            const [fh, fm] = this.form.appointment_time.split(':').map(Number);
            const currentMins = fh * 60 + fm;
            let nextApt = null;
            let nextMins = Infinity;
            for (const apt of dayApps) {
                if (this.editingAppointment && apt.id === this.editingAppointment.id) continue;
                if (String(apt.professional.id) !== String(this.form.professional_id)) continue;
                const d = new Date(apt.appointment_date);
                const aptMins = d.getHours() * 60 + d.getMinutes();
                if (aptMins > currentMins && aptMins < nextMins) {
                    nextMins = aptMins;
                    nextApt = apt;
                }
            }
            if (!nextApt) return null;
            return { maxMins: nextMins - currentMins, time: this.formatTime(nextApt.appointment_date) };
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

            // Si la duración actual supera el tiempo disponible hasta el próximo turno,
            // reducirla automáticamente a la mayor opción permitida.
            const constraint = this.nextAppointmentConstraint;
            if (constraint && parseInt(this.form.duration) > constraint.maxMins) {
                const best = [...this.durationOptions].reverse().find(o => o.value <= constraint.maxMins);
                this.form.duration = best ? best.value : 5;
            }

            return true;
        },

        async submitForm() {
            // Validate datetime before submitting
            if (!this.validateDateTime()) {
                return;
            }

            // Validar que la duración no superponga con el siguiente turno
            const constraint = this.nextAppointmentConstraint;
            if (constraint && parseInt(this.form.duration) > constraint.maxMins) {
                this.showNotification(
                    `La duración elegida (${this.form.duration} min) superaría el siguiente turno de las ${constraint.time}. Máximo disponible: ${constraint.maxMins} min.`,
                    'error'
                );
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
                    let value = this.form[key];

                    // Convertir booleanos a enteros para evitar problemas con FormData
                    if (typeof value === 'boolean') {
                        value = value ? 1 : 0;
                    }

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

        openDayModal(date, professionalId) {
            this.selectedDayDate = date;
            this.selectedProfessionalId = professionalId;
            
            // Find professional name
            const professional = this.professionals.find(p => p.id == professionalId);
            this.selectedProfessionalName = professional ? professional.first_name + ' ' + professional.last_name : '';
            
            // Get appointments for this day
            this.dayAppointments = this.allAppointments[date] || [];

            // Calcular horario del día para el timeline
            const [y, m, d] = date.split('-');
            const dateObj = new Date(+y, +m - 1, +d);
            const jsDay = dateObj.getDay();          // 0=Dom, 1=Lun…6=Sab
            const dayKey = jsDay === 0 ? 7 : jsDay; // → 1=Lun…7=Dom (igual a Laravel)
            const sched = this.schedules[dayKey];

            if (sched) {
                const [sh, sm] = sched.start_time.split(':').map(Number);
                const [eh, em] = sched.end_time.split(':').map(Number);
                this.daySchedule = {
                    startMins: sh * 60 + sm,
                    endMins:   eh * 60 + em,
                    totalMins: (eh * 60 + em) - (sh * 60 + sm),
                    startTime: sched.start_time,
                    endTime:   sched.end_time,
                };
            } else {
                this.daySchedule = null;
            }

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

        // Horas del timeline (marcas cada 60 min)
        get timelineHours() {
            if (!this.daySchedule) return [];
            const hours = [];
            const startH = Math.floor(this.daySchedule.startMins / 60);
            const endH   = Math.ceil(this.daySchedule.endMins / 60);
            for (let h = startH; h <= endH; h++) {
                hours.push({
                    label: String(h).padStart(2, '0') + ':00',
                    topPx: (h * 60 - this.daySchedule.startMins) * this.pxPerMin,
                });
            }
            return hours;
        },

        // Marcas de media hora (solo líneas, sin label)
        get timelineHalfHours() {
            if (!this.daySchedule) return [];
            const halves = [];
            const startH = Math.floor(this.daySchedule.startMins / 60);
            const endH   = Math.ceil(this.daySchedule.endMins / 60);
            for (let h = startH; h < endH; h++) {
                const topPx = (h * 60 + 30 - this.daySchedule.startMins) * this.pxPerMin;
                if (topPx > 0 && topPx < this.daySchedule.totalMins * this.pxPerMin) {
                    halves.push({ topPx });
                }
            }
            return halves;
        },

        // Layout unificado con posicionamiento TEMPORAL PURO.
        // Appointments y slots libres usan el mismo sistema de coordenadas que
        // las marcas de hora → el grid siempre está alineado con el contenido.
        get timelineLayout() {
            if (!this.daySchedule) return { items: [] };
            const { startMins, endMins } = this.daySchedule;
            const px = (mins) => mins * this.pxPerMin;
            const isPast = this.isDayInPast();
            const SLOT = 30;
            const MIN_FREE = 5;

            // Ordenar turnos y calcular posiciones basadas en tiempo
            const sortedApts = [...this.dayAppointments]
                .map(apt => {
                    const d = new Date(apt.appointment_date);
                    const s = d.getHours() * 60 + d.getMinutes();
                    const dur = apt.duration > 0 ? apt.duration : 0;
                    return { apt, sMins: s, dur };
                })
                .sort((a, b) => a.sMins - b.sMins);

            const items = [];

            // Bloques de turnos: topPx y heightPx derivados del tiempo real
            for (const { apt, sMins, dur } of sortedApts) {
                items.push({
                    type: 'appointment',
                    topPx: px(sMins - startMins),
                    heightPx: Math.max(px(dur), 24), // 24px mínimo solo para urgencias/turnos <8min
                    apt,
                });
            }

            // Slots libres: tiempo real → mismas coordenadas que el grid
            if (!isPast) {
                // Fusionar intervalos ocupados
                const busy = sortedApts
                    .map(({ sMins, dur }) => [sMins, sMins + dur])
                    .reduce((acc, [s, e]) => {
                        if (acc.length && s <= acc[acc.length - 1][1]) {
                            acc[acc.length - 1][1] = Math.max(acc[acc.length - 1][1], e);
                        } else {
                            acc.push([s, e]);
                        }
                        return acc;
                    }, []);

                // Encontrar segmentos libres y dividirlos en chunks de 30 min
                const freeSegs = [];
                let prev = startMins;
                for (const [s, e] of busy) {
                    if (s > prev) freeSegs.push([prev, s]);
                    prev = Math.max(prev, e);
                }
                if (prev < endMins) freeSegs.push([prev, endMins]);

                for (const [segStart, segEnd] of freeSegs) {
                    let cur = segStart;
                    while (cur < segEnd) {
                        const nextB    = (Math.floor(cur / SLOT) + 1) * SLOT;
                        const chunkEnd = Math.min(nextB, segEnd);
                        const chunkMins = chunkEnd - cur;
                        if (chunkMins >= MIN_FREE) {
                            const h = Math.floor(cur / 60);
                            const m = cur % 60;
                            items.push({
                                type: 'free',
                                topPx: px(cur - startMins),
                                heightPx: Math.max(px(chunkMins) - 2, 20),
                                startMins: cur,
                                label: String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'),
                            });
                        }
                        cur = chunkEnd;
                    }
                }
            }

            return { items };
        },

        get timelineHeightPx() {
            return this.daySchedule ? this.daySchedule.totalMins * this.pxPerMin : 0;
        },

        appointmentBlockClass(apt) {
            if (apt.duration === 0) {
                return 'bg-red-500 border-l-4 border-red-700 text-white ring-1 ring-red-300';
            }
            if (apt.is_between_turn) {
                return 'bg-orange-400 border-l-4 border-orange-600 text-white';
            }
            const map = {
                scheduled: 'bg-blue-500 border-l-4 border-blue-700 text-white',
                attended:  'bg-green-500 border-l-4 border-green-700 text-white',
                absent:    'bg-orange-500 border-l-4 border-orange-700 text-white',
            };
            return map[apt.status] || 'bg-gray-400 border-l-4 border-gray-600 text-white';
        },

        formatDateSpanish(dateString) {
            // Parse como fecha local para evitar problemas de timezone
            const [year, month, day] = dateString.split('-');
            const date = new Date(year, month - 1, day);
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
            // Comparar solo las fechas como strings para evitar problemas de timezone
            const today = this.getTodayDate();
            return this.selectedDayDate < today;
        },

        isAppointmentInPast(appointment) {
            if (!appointment || !appointment.appointment_date) return false;
            const appointmentDate = new Date(appointment.appointment_date);
            const now = new Date();
            return appointmentDate < now;
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

        // Función para normalizar texto (quitar acentos)
        function normalizeText(text) {
            return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
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

                    // Normalizar el término de búsqueda (quitar acentos y lowercase)
                    const searchTerm = normalizeText(params.term);
                    const text = normalizeText(data.text || '');

                    // Obtener atributos data del elemento option y normalizarlos
                    const $option = $(data.element);
                    const dni = normalizeText($option.attr('data-dni') || '');
                    const firstName = normalizeText($option.attr('data-first-name') || '');
                    const lastName = normalizeText($option.attr('data-last-name') || '');

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

// Componente Alpine.js para el modal de pacientes
function patientModal() {
    return {
        modalOpen: false,
        editingPatient: null,
        loading: false,

        form: {
            first_name: '',
            last_name: '',
            dni: '',
            birth_date: '',
            email: '',
            phone: '',
            address: '',
            health_insurance: '',
            health_insurance_number: '',
            titular_obra_social: '',
            plan_obra_social: ''
        },

        init() {
            // Escuchar evento para abrir el modal
            this.$watch('modalOpen', (value) => {
                if (!value) {
                    this.editingPatient = null;
                    this.resetForm();
                }
            });

            // Escuchar evento personalizado
            window.addEventListener('open-patient-modal', () => {
                this.openModal();
            });

            // Seleccionar paciente recién creado si existe
            const newPatientId = sessionStorage.getItem('newPatientId');
            if (newPatientId) {
                setTimeout(() => {
                    $('#patient-select').val(newPatientId).trigger('change');
                    sessionStorage.removeItem('newPatientId');
                }, 1000);
            }
        },

        openModal() {
            this.resetForm();
            this.modalOpen = true;
        },

        resetForm() {
            this.form = {
                first_name: '',
                last_name: '',
                dni: '',
                birth_date: '',
                email: '',
                phone: '',
                address: '',
                health_insurance: '',
                health_insurance_number: '',
                titular_obra_social: '',
                plan_obra_social: ''
            };
        },

        async submitForm() {
            this.loading = true;

            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('/patients', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.modalOpen = false;

                    // Mostrar notificación de éxito
                    window.showToast('Paciente creado exitosamente. La página se recargará para actualizar la lista.', 'success');

                    // Guardar el ID del nuevo paciente para seleccionarlo después de recargar
                    if (result.patient && result.patient.id) {
                        sessionStorage.setItem('newPatientId', result.patient.id);
                    }

                    // Recargar la página
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    if (response.status === 422 && result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        window.showToast('Errores de validación: ' + errorMessages, 'error');
                    } else {
                        window.showToast(result.message || 'Error al guardar el paciente', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                window.showToast('Error al guardar el paciente', 'error');
            } finally {
                this.loading = false;
            }
        },

        calculateAge(birthDate) {
            if (!birthDate) return '';
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age;
        },

        getMaxDate() {
            return new Date().toISOString().split('T')[0];
        }
    }
}
</script>
@endpush
@endsection