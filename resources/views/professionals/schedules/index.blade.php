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

    <!-- Quick Actions -->
    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200">Acciones Rápidas</h3>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Configuraciones predeterminadas para agilizar la configuración inicial.</p>
            </div>
            <div class="flex flex-wrap gap-2">
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
    </div>

    <!-- Schedule Configuration -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Días y Horarios de Atención
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Configure los días de la semana y horarios en los que el profesional atiende.</p>
            
            <form @submit.prevent="saveSchedules()">
                <div class="space-y-4">
                    @foreach($daysOfWeek as $dayNumber => $dayName)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <!-- Day Name and Toggle -->
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" 
                                               x-model="schedules[{{ $dayNumber }}].enabled"
                                               @change="toggleDay({{ $dayNumber }})"
                                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $dayName }}
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Time Controls -->
                                <div x-show="schedules[{{ $dayNumber }}].enabled" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="flex items-center gap-4">
                                    
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">Desde:</label>
                                        <input type="time" 
                                               x-model="schedules[{{ $dayNumber }}].start_time"
                                               class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                               required>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">Hasta:</label>
                                        <input type="time" 
                                               x-model="schedules[{{ $dayNumber }}].end_time"
                                               class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                               required>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">Consultorio:</label>
                                        <select x-model="schedules[{{ $dayNumber }}].office_id"
                                                class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            <option value="">Sin consultorio</option>
                                            @foreach($offices as $office)
                                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function professionalSchedules() {
    return {
        loading: false,
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
@endsection