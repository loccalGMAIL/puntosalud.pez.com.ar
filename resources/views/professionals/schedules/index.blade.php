@extends('layouts.app')

@section('title', 'Horarios - ' . $professional->full_name . ' - ' . config('app.name'))
@section('mobileTitle', 'Horarios')

@section('content')
<div class="p-6" x-data="professionalSchedules()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('professionals.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Profesionales</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>{{ $professional->full_name }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Horarios</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configurar Horarios</h1>
            <p class="text-gray-600 dark:text-gray-400">Dr. {{ $professional->full_name }} - {{ $professional->specialty->name }}</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('professionals.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver
            </a>
            <button @click="$dispatch('open-absence-modal')"
                    class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                </svg>
                Calendario de Asistencia
            </button>
            <button @click="saveSchedules()" 
                    :disabled="loading"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-show="!loading">Guardar Horarios</span>
                <span x-show="loading">Guardando...</span>
            </button>
        </div>
    </div>

    <!-- Quick Actions (acordeón) -->
    <div class="mb-4 border border-blue-200 dark:border-blue-800 rounded-lg overflow-hidden">
        <button @click="quickActionsOpen = !quickActionsOpen"
                type="button"
                class="w-full flex items-center justify-between px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-left">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-200">Acciones Rápidas</span>
                <span class="text-xs text-blue-500 dark:text-blue-400">Plantillas predeterminadas</span>
            </div>
            <svg class="w-4 h-4 text-blue-500 dark:text-blue-400 transition-transform duration-200"
                 :class="quickActionsOpen ? 'rotate-180' : ''"
                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
        <div x-show="quickActionsOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="px-4 py-3 bg-blue-50/50 dark:bg-blue-900/10 border-t border-blue-200 dark:border-blue-800 flex flex-wrap gap-2">
            <button @click="setWeekdaySchedule()"
                    class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-white border border-blue-300 rounded hover:bg-blue-50 dark:bg-gray-800 dark:text-blue-300 dark:border-blue-600 dark:hover:bg-gray-700 transition-colors">
                <span class="hidden sm:inline">Horario de Oficina:</span> Lun-Vie 9:00-17:00
            </button>
            <button @click="setFullWeekSchedule()"
                    class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-white border border-blue-300 rounded hover:bg-blue-50 dark:bg-gray-800 dark:text-blue-300 dark:border-blue-600 dark:hover:bg-gray-700 transition-colors">
                <span class="hidden sm:inline">Semana Completa:</span> Lun-Sáb 9:00-17:00
            </button>
            <button @click="setMorningSchedule()"
                    class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-white border border-blue-300 rounded hover:bg-blue-50 dark:bg-gray-800 dark:text-blue-300 dark:border-blue-600 dark:hover:bg-gray-700 transition-colors">
                <span class="hidden sm:inline">Solo Mañanas:</span> Lun-Vie 9:00-13:00
            </button>
            <button @click="clearAllSchedules()"
                    class="px-3 py-1.5 text-xs font-medium text-red-700 bg-white border border-red-300 rounded hover:bg-red-50 dark:bg-gray-800 dark:text-red-300 dark:border-red-600 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-3 h-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                Limpiar Todo
            </button>
        </div>
    </div>

    <!-- Schedule Configuration -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Días y Horarios de Atención</h2>
        </div>
        <form @submit.prevent="saveSchedules()" class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($daysOfWeek as $dayNumber => $dayName)
                <div class="flex items-center justify-between px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    <!-- Día + checkbox -->
                    <div class="flex items-center gap-3 w-28 shrink-0">
                        <input type="checkbox"
                               x-model="schedules[{{ $dayNumber }}].enabled"
                               @change="toggleDay({{ $dayNumber }})"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label class="text-sm font-medium text-gray-900 dark:text-white select-none">{{ $dayName }}</label>
                    </div>

                    <!-- Controles de horario -->
                    <div x-show="schedules[{{ $dayNumber }}].enabled"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs text-gray-500 dark:text-gray-400">Desde</label>
                            <input type="time"
                                   x-model="schedules[{{ $dayNumber }}].start_time"
                                   class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs text-gray-500 dark:text-gray-400">Hasta</label>
                            <input type="time"
                                   x-model="schedules[{{ $dayNumber }}].end_time"
                                   class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs text-gray-500 dark:text-gray-400">Consultorio</label>
                            <select x-model="schedules[{{ $dayNumber }}].office_id"
                                    class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">—</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Placeholder cuando está deshabilitado -->
                    <div x-show="!schedules[{{ $dayNumber }}].enabled" class="text-xs text-gray-400 dark:text-gray-500 italic">
                        Sin atención
                    </div>
                </div>
            @endforeach
        </form>
    </div>
</div>

<script>
function professionalSchedules() {
    return {
        loading: false,
        quickActionsOpen: false,
        schedules: {
            @foreach($daysOfWeek as $dayNumber => $dayName)
            {{ $dayNumber }}: {
                day_of_week: {{ $dayNumber }},
                enabled: {{ isset($schedules[$dayNumber]) ? 'true' : 'false' }},
                start_time: '{{ isset($schedules[$dayNumber]) ? $schedules[$dayNumber]->start_time->format('H:i') : '09:00' }}',
                end_time: '{{ isset($schedules[$dayNumber]) ? $schedules[$dayNumber]->end_time->format('H:i') : '17:00' }}',
                office_id: '{{ isset($schedules[$dayNumber]) ? $schedules[$dayNumber]->office_id : '' }}'
            },
            @endforeach
        },

        init() {
            console.log('Professional schedules initialized');
        },

        toggleDay(dayNumber) {
            if (!this.schedules[dayNumber].enabled) {
                this.schedules[dayNumber].start_time = '09:00';
                this.schedules[dayNumber].end_time = '17:00';
                this.schedules[dayNumber].office_id = '';
            }
        },

        setWeekdaySchedule() {
            // Lunes a Viernes 9:00-17:00
            for (let day = 1; day <= 5; day++) {
                this.schedules[day].enabled = true;
                this.schedules[day].start_time = '09:00';
                this.schedules[day].end_time = '17:00';
            }
            // Deshabilitar fines de semana
            this.schedules[6].enabled = false;
            this.schedules[7].enabled = false;
        },

        setFullWeekSchedule() {
            // Lunes a Viernes 9:00-17:00
            for (let day = 1; day <= 5; day++) {
                this.schedules[day].enabled = true;
                this.schedules[day].start_time = '09:00';
                this.schedules[day].end_time = '17:00';
            }
            // Sábado 8:00-15:00
            this.schedules[6].enabled = true;
            this.schedules[6].start_time = '08:00';
            this.schedules[6].end_time = '15:00';
            // Deshabilitar Domingo
            this.schedules[7].enabled = false;
        },

        setMorningSchedule() {
            // Lunes a Viernes solo mañanas
            for (let day = 1; day <= 5; day++) {
                this.schedules[day].enabled = true;
                this.schedules[day].start_time = '09:00';
                this.schedules[day].end_time = '13:00';
            }
            // Deshabilitar fines de semana
            this.schedules[6].enabled = false;
            this.schedules[7].enabled = false;
        },

        clearAllSchedules() {
            if (confirm('¿Está seguro de que desea limpiar todos los horarios?')) {
                for (let day = 1; day <= 7; day++) {
                    this.schedules[day].enabled = false;
                    this.schedules[day].start_time = '09:00';
                    this.schedules[day].end_time = '17:00';
                    this.schedules[day].office_id = '';
                }
            }
        },

        async saveSchedules() {
            this.loading = true;
            
            try {
                const formData = new FormData();
                
                // Preparar datos de horarios
                const schedulesData = {};
                Object.keys(this.schedules).forEach(dayNumber => {
                    const schedule = this.schedules[dayNumber];
                    schedulesData[dayNumber] = {
                        day_of_week: parseInt(dayNumber),
                        enabled: schedule.enabled,
                        start_time: schedule.start_time,
                        end_time: schedule.end_time,
                        office_id: schedule.office_id || null
                    };
                });
                
                formData.append('schedules', JSON.stringify(schedulesData));
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch('{{ route("professionals.schedules.store", $professional) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification(result.message || 'Error al guardar los horarios', 'error');
                }
            } catch (error) {
                this.showNotification('Error al guardar los horarios', 'error');
            } finally {
                this.loading = false;
            }
        },

        showNotification(message, type = 'info') {
            window.showToast(message, type);
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

{{-- Modal de Ausencias --}}
<div x-data="absenceModal()" @open-absence-modal.window="open()" @keydown.escape.window="show = false" x-cloak>
    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">

        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <button @click="prevMonth()" :disabled="loading"
                            class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 disabled:opacity-40 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white w-36 text-center capitalize"
                          x-text="calData ? calData.monthName : '...'"></span>
                    <button @click="nextMonth()" :disabled="loading"
                            class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 disabled:opacity-40 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Calendario de Asistencia</span>
                    <button @click="show = false"
                            class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Calendario --}}
            <div class="p-4">
                {{-- Encabezados días --}}
                <div class="grid grid-cols-6 mb-1">
                    <template x-for="name in ['Lun','Mar','Mié','Jue','Vie','Sáb']" :key="name">
                        <div class="text-center text-xs font-medium text-gray-400 dark:text-gray-500 py-1" x-text="name"></div>
                    </template>
                </div>

                {{-- Loading --}}
                <div x-show="loading" class="h-44 flex items-center justify-center">
                    <svg class="animate-spin h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- Grid de días --}}
                <div x-show="!loading && calData" class="grid grid-cols-6 gap-1">
                    <template x-for="day in (calData ? calData.days : [])" :key="day.date">
                        <div @click="toggleDay(day)"
                             :class="cellClass(day)"
                             class="relative rounded-lg p-1.5 min-h-[52px] flex flex-col items-center transition-colors select-none">

                            {{-- Spinner por día --}}
                            <div x-show="loadingDay === day.date"
                                 class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-800/70 rounded-lg z-10">
                                <svg class="animate-spin h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            {{-- Número de día --}}
                            <span :class="day.isToday
                                    ? 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold'
                                    : 'text-sm font-medium leading-6'"
                                  x-text="day.day"></span>

                            {{-- Indicador Ausente --}}
                            <span x-show="day.isAbsent && day.isCurrentMonth"
                                  class="mt-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400 leading-tight">
                                Ausente
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer / Leyenda --}}
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-x-4 gap-y-1.5">
                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <div class="w-3 h-3 rounded bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-500"></div>
                    <span>Disponible</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <div class="w-3 h-3 rounded bg-amber-100 dark:bg-amber-900/40 border border-amber-300"></div>
                    <span>Ausente</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <div class="w-3 h-3 rounded bg-red-50 border border-red-200"></div>
                    <span>Feriado</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <div class="w-3 h-3 rounded bg-gray-200 dark:bg-gray-600"></div>
                    <span>Sin horario</span>
                </div>
                <div class="ml-auto">
                    <button @click="show = false"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function absenceModal() {
    return {
        show: false,
        loading: false,
        loadingDay: null,
        calData: null,

        open() {
            this.show = true;
            if (!this.calData) {
                const now = new Date();
                this.loadMonth(now.getFullYear(), now.getMonth() + 1);
            }
        },

        async loadMonth(year, month) {
            this.loading = true;
            try {
                const url = '{{ route('professionals.absences.month', $professional) }}' + `?year=${year}&month=${month}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.calData = await res.json();
            } catch (e) {
                window.showToast('Error cargando el calendario', 'error');
            } finally {
                this.loading = false;
            }
        },

        prevMonth() {
            if (this.calData && !this.loading) {
                this.loadMonth(this.calData.prevYear, this.calData.prevMonth);
            }
        },

        nextMonth() {
            if (this.calData && !this.loading) {
                this.loadMonth(this.calData.nextYear, this.calData.nextMonth);
            }
        },

        async toggleDay(day) {
            if (!day.isCurrentMonth || !day.hasSchedule || day.isHoliday || this.loadingDay) return;

            this.loadingDay = day.date;
            try {
                const res = await fetch('{{ route('professionals.absences.toggle', $professional) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ date: day.date }),
                });
                const result = await res.json();
                if (result.success) {
                    day.isAbsent = result.status === 'added';
                    window.showToast(result.status === 'added' ? 'Ausencia registrada' : 'Ausencia eliminada', 'success');
                }
            } catch (e) {
                window.showToast('Error de conexión', 'error');
            } finally {
                this.loadingDay = null;
            }
        },

        cellClass(day) {
            if (!day.isCurrentMonth) {
                return 'bg-gray-50 dark:bg-gray-900/50 text-gray-300 dark:text-gray-600';
            }
            if (day.isHoliday) {
                return 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400';
            }
            if (!day.hasSchedule) {
                return 'bg-gray-200 dark:bg-gray-600 text-gray-400 dark:text-gray-400';
            }
            if (day.isAbsent) {
                return 'bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-600 text-amber-800 dark:text-amber-300 cursor-pointer hover:bg-amber-200 dark:hover:bg-amber-900/50';
            }
            return 'bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600';
        },
    };
}
</script>
@endsection