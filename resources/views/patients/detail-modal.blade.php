<!-- Modal de Detalle del Paciente -->
<div x-show="detailModalOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="detailModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="closeDetailModal()"></div>

        <!-- Modal Content -->
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
        <div x-show="detailModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-4xl transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">

            <!-- Header -->
            <div class="bg-emerald-50 dark:bg-emerald-950/20 px-6 py-4 border-b border-emerald-200/50 dark:border-emerald-800/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Detalle del Paciente</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Información completa del paciente</p>
                        </div>
                    </div>
                    <button @click="closeDetailModal()"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                <template x-if="viewingPatient">
                    <div class="space-y-6">
                        <!-- Información del Paciente -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información Personal</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre Completo</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.last_name + ', ' + viewingPatient.first_name"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">DNI</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.dni"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Nacimiento</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="new Date(viewingPatient.birth_date).toLocaleDateString('es-AR')"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Edad</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="calculateAge(viewingPatient.birth_date) + ' años'"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.phone || 'No especificado'"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.email || 'No especificado'"></p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.address || 'No especificada'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Obra Social -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4" x-show="viewingPatient.health_insurance">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información de Obra Social</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Obra Social</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.health_insurance"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Afiliado</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.health_insurance_number || 'No especificado'"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Titular</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.titular_obra_social || 'No especificado'"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</label>
                                    <p class="text-base text-gray-900 dark:text-white" x-text="viewingPatient.plan_obra_social || 'No especificado'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Turnos del Paciente -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial de Turnos</h4>

                            <!-- Loading -->
                            <div x-show="loadingDetail" class="text-center py-8">
                                <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Cargando turnos...</p>
                            </div>

                            <!-- Lista de turnos -->
                            <div x-show="!loadingDetail" class="space-y-3">
                                <template x-if="patientAppointments.length === 0">
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                                        </svg>
                                        <p class="text-gray-600 dark:text-gray-400">No hay turnos registrados</p>
                                    </div>
                                </template>

                                <template x-for="appointment in patientAppointments" :key="appointment.id">
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-white dark:hover:bg-gray-600/50 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="new Date(appointment.appointment_date).toLocaleDateString('es-AR', {day: '2-digit', month: '2-digit', year: 'numeric'})"></span>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="new Date(appointment.appointment_date).toLocaleTimeString('es-AR', {hour: '2-digit', minute: '2-digit'}) + 'hs'"></span>
                                                    <span :class="{
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': appointment.status === 'scheduled',
                                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': appointment.status === 'attended',
                                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300': appointment.status === 'cancelled',
                                                        'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300': appointment.status === 'absent'
                                                    }"
                                                    class="px-2 py-0.5 text-xs font-medium rounded-full"
                                                    x-text="{
                                                        'scheduled': 'Programado',
                                                        'attended': 'Atendido',
                                                        'cancelled': 'Cancelado',
                                                        'absent': 'Ausente'
                                                    }[appointment.status]"></span>
                                                </div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    <span class="font-medium">Profesional:</span>
                                                    <span x-text="'Dr. ' + appointment.professional.first_name + ' ' + appointment.professional.last_name"></span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2 text-sm">
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Monto:</span>
                                                        <span class="font-medium text-gray-900 dark:text-white" x-text="appointment.final_amount ? '$' + parseFloat(appointment.final_amount).toFixed(2) : (appointment.estimated_amount ? '$' + parseFloat(appointment.estimated_amount).toFixed(2) : 'Sin especificar')"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Comprobante:</span>
                                                        <span class="font-medium text-gray-900 dark:text-white" x-text="appointment.payment_receipt || 'Sin comprobante'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                <div class="flex justify-end">
                    <button @click="closeDetailModal()"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
