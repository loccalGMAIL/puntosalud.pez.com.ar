{{-- Modal de detalles del día con timeline --}}
<div x-show="dayModalOpen"
     x-cloak
     class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">

    <!-- Modal Content -->
    <div @click.away="dayModalOpen = false"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
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
        <div class="p-6 flex-1 min-h-0 flex flex-col">
            <!-- Barra superior: acción + info de horario -->
            <div class="flex-shrink-0 flex items-center justify-between mb-4 flex-wrap gap-3">
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
            <div x-show="daySchedule" class="flex-shrink-0 flex flex-wrap gap-x-4 gap-y-1 mb-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span> Programado</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Atendido</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span> Ausente</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Urgencia</span>
                <span x-show="!isDayInPast()" class="flex items-center gap-1"><span class="w-3 h-2 rounded border border-dashed border-emerald-500 inline-block bg-emerald-50 dark:bg-emerald-900/20"></span> Disponible</span>
            </div>

            <!-- Timeline a ancho completo -->
            <div x-show="daySchedule" class="flex-1 min-h-0 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
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
                                <span class="text-[14px] font-bold whitespace-nowrap tabular-nums" x-text="formatTime(item.apt.appointment_date)"></span>
                                <span class="opacity-40 select-none text-[13px]">|</span>
                                <span class="flex-1 truncate text-[14px]"
                                      x-text="item.apt.patient.last_name + ', ' + item.apt.patient.first_name"
                                      :title="item.apt.patient.last_name + ', ' + item.apt.patient.first_name"></span>
                                <span x-show="item.apt.notes"
                                      :title="item.apt.notes"
                                      class="flex-shrink-0 cursor-help text-amber-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </span>
                                <span class="whitespace-nowrap opacity-70 text-[13px] tabular-nums" x-text="item.apt.duration > 0 ? item.apt.duration + ' min' : '🚨'"></span>
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
        <div class="flex-shrink-0 bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end border-t border-gray-200 dark:border-gray-600">
            <button @click="dayModalOpen = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Cerrar
            </button>
        </div>
    </div>
</div>
