@if(!$selectedProfessional)
    {{-- Sin profesional seleccionado: cards de profesionales frecuentes --}}
    <div class="space-y-6">

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
    {{-- Profesional seleccionado: grid del calendario --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <!-- Calendar Header -->
        <div class="grid grid-cols-6 bg-gray-50 dark:bg-gray-700">
            @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $dayName)
                <div class="py-2 px-1 text-center font-semibold text-xs text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600 last:border-r-0">
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

                    // Verificar si el profesional está ausente ese día
                    $isAbsent = $professionalAbsences->has($dayKey);

                    // Verificar si hay cumpleaños
                    $hasBirthdays = $birthdays->has($dayKey);
                    $birthdayProfessionals = $hasBirthdays ? $birthdays->get($dayKey) : collect();
                    $birthdaysText = $birthdayProfessionals->map(fn($p) => "Dr. {$p['name']} ({$p['age']} años)")->join(', ');

                    // Conteos por estado (para inline + tooltip)
                    $cntScheduled = $dayAppointments->where('is_urgency', false)->where('status', 'scheduled')->count();
                    $cntAttended  = $dayAppointments->where('is_urgency', false)->where('status', 'attended')->count();
                    $cntAbsent    = $dayAppointments->where('is_urgency', false)->where('status', 'absent')->count();
                    $cntUrgency   = $dayAppointments->where('is_urgency', true)->count();
                    $hasApts      = $hasSchedule && $dayAppointments->count() > 0;

                    // Posición horizontal del tooltip según columna
                    $tooltipAlign = match($dayOfWeek) {
                        1       => 'left-0',              // Lunes: alineado a la izquierda
                        6       => 'right-0',             // Sábado: alineado a la derecha
                        default => 'left-1/2 -translate-x-1/2', // resto: centrado
                    };
                    $tooltipArrow = match($dayOfWeek) {
                        1       => 'left-4',
                        6       => 'right-4',
                        default => 'left-1/2 -translate-x-1/2',
                    };
                @endphp

                <div class="aspect-square p-1.5 relative group border-r border-b border-gray-200 dark:border-gray-600 last:border-r-0
                            {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-900' :
                               ($isHoliday ? 'bg-red-50/70 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800' :
                               (!$hasSchedule || $isAbsent ? 'bg-gray-300 dark:bg-gray-600' : 'bg-white dark:bg-gray-800')) }}
                            {{ ($hasSchedule && $isCurrentMonth && !$isHoliday && !$isAbsent) ? 'cursor-pointer hover:brightness-95' : '' }}"
                     @if($hasSchedule && $isCurrentMonth && !$isHoliday && !$isAbsent)
                         onclick="openDayModal('{{ $currentDay->format('Y-m-d') }}', {{ $selectedProfessional }})"
                     @endif>

                    {{-- Contenido de la celda (recortado) --}}
                    <div class="overflow-hidden h-full">

                        <!-- Day Number -->
                        <div class="flex items-center gap-0.5 mb-0.5">
                            <span class="text-xs font-medium
                                        {{ !$isCurrentMonth ? 'text-gray-400 dark:text-gray-600' :
                                           ($isHoliday ? 'text-red-700 dark:text-red-400' :
                                           (!$hasSchedule ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white')) }}
                                        {{ $isToday ? 'bg-blue-600 !text-white rounded-full w-5 h-5 flex items-center justify-center text-[11px]' : '' }}">
                                {{ $currentDay->day }}
                            </span>
                            @if($isHoliday)
                                <svg class="w-3 h-3 text-red-600 dark:text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                            @if($hasBirthdays)
                                <span class="text-xs cursor-help shrink-0" title="🎉 Cumpleaños: {{ $birthdaysText }}">🎂</span>
                            @endif
                        </div>

                        <!-- Holiday Label -->
                        @if($isHoliday)
                            <div class="text-[10px] leading-tight text-red-700 dark:text-red-400 truncate">
                                {{ Str::limit($holidayData->reason, 12) }}
                            </div>
                        @endif

                        <!-- Absent Label -->
                        @if($isAbsent && $isCurrentMonth)
                            <div class="text-[10px] leading-tight text-gray-600 dark:text-gray-400">
                                Ausente
                            </div>
                        @endif

                        <!-- Puntos con cantidad por estado -->
                        @if($hasApts)
                            <div class="flex flex-col gap-0.5 mt-1">
                                @if($cntScheduled > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400 shrink-0"></span>
                                        <span class="text-[10px] leading-none text-gray-600 dark:text-gray-400">{{ $cntScheduled }}</span>
                                    </div>
                                @endif
                                @if($cntAttended > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 dark:bg-green-400 shrink-0"></span>
                                        <span class="text-[10px] leading-none text-gray-600 dark:text-gray-400">{{ $cntAttended }}</span>
                                    </div>
                                @endif
                                @if($cntAbsent > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400 dark:bg-orange-500 shrink-0"></span>
                                        <span class="text-[10px] leading-none text-gray-600 dark:text-gray-400">{{ $cntAbsent }}</span>
                                    </div>
                                @endif
                                @if($cntUrgency > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400 shrink-0"></span>
                                        <span class="text-[10px] leading-none text-gray-600 dark:text-gray-400">{{ $cntUrgency }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>{{-- /contenido --}}

                    {{-- Tooltip al hover (solo si hay turnos) --}}
                    @if($hasApts)
                        <div class="absolute bottom-full {{ $tooltipAlign }} mb-2 z-50
                                    invisible group-hover:visible opacity-0 group-hover:opacity-100
                                    transition-opacity duration-150
                                    bg-gray-900 dark:bg-gray-700 text-white rounded-lg shadow-xl
                                    px-3 py-2 text-xs whitespace-nowrap pointer-events-none">
                            @if($cntScheduled > 0)
                                <div class="flex items-center gap-1.5 py-0.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                                    Programados: {{ $cntScheduled }}
                                </div>
                            @endif
                            @if($cntAttended > 0)
                                <div class="flex items-center gap-1.5 py-0.5">
                                    <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>
                                    Atendidos: {{ $cntAttended }}
                                </div>
                            @endif
                            @if($cntAbsent > 0)
                                <div class="flex items-center gap-1.5 py-0.5">
                                    <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                                    Ausentes: {{ $cntAbsent }}
                                </div>
                            @endif
                            @if($cntUrgency > 0)
                                <div class="flex items-center gap-1.5 py-0.5">
                                    <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                                    Urgencias: {{ $cntUrgency }}
                                </div>
                            @endif
                            {{-- Flecha del tooltip --}}
                            <div class="absolute top-full {{ $tooltipArrow }}
                                        border-[5px] border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
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
            <div class="w-3 h-3 bg-blue-500 dark:bg-blue-400 rounded-full"></div>
            <span class="text-gray-700 dark:text-gray-300">Programado</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-green-500 dark:bg-green-400 rounded-full"></div>
            <span class="text-gray-700 dark:text-gray-300">Atendido</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-orange-400 dark:bg-orange-500 rounded-full"></div>
            <span class="text-gray-700 dark:text-gray-300">Ausente</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded"></div>
            <span class="text-gray-700 dark:text-gray-300">Feriado</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-gray-300 dark:bg-gray-600 rounded"></div>
            <span class="text-gray-700 dark:text-gray-300">Ausente / Sin atención</span>
        </div>
    </div>
@endif
