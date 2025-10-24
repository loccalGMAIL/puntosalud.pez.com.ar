<!-- Modal de Entreturno/Urgencia -->
<div x-show="urgencyModalOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-30 flex items-center justify-center p-4">

    <!-- Modal Content -->
    <div @click.away="urgencyModalOpen = false"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[85vh] overflow-y-auto">

        <!-- Header -->
        <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 px-5 py-3 border-b border-red-200 dark:border-red-700">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-semibold text-red-900 dark:text-red-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                    </svg>
                    <span>Entreturno / Urgencia</span>
                </h3>
                <button @click="urgencyModalOpen = false" class="text-red-400 hover:text-red-600 dark:hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <!-- Información de urgencia -->
            <div class="bg-red-100 dark:bg-red-900/30 p-2 rounded border border-red-300 dark:border-red-700">
                <div class="flex items-center gap-2 text-xs text-red-900 dark:text-red-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span><strong>Atención urgente:</strong> Seleccione fecha desde hoy en adelante.</span>
                </div>
            </div>
        </div>

        <!-- Body -->
        <form @submit.prevent="submitUrgencyForm()" class="p-4 space-y-3">
            <!-- Profesional -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profesional *</label>
                <select id="urgency-professional-select"
                        x-model="urgencyForm.professional_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                        required>
                    <option value="">Seleccionar profesional...</option>
                    <template x-for="professional in professionals" :key="professional.id">
                        <option :value="professional.id"
                                :data-specialty="professional.specialty ? professional.specialty.name : ''"
                                x-text="'Dr. ' + professional.first_name + ' ' + professional.last_name + (professional.specialty ? ' - ' + professional.specialty.name : '')"></option>
                    </template>
                </select>
            </div>

            <!-- Paciente -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paciente *</label>
                <select id="urgency-patient-select"
                        x-model="urgencyForm.patient_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                        required>
                    <option value="">Seleccionar paciente...</option>
                    <template x-for="patient in patients" :key="patient.id">
                        <option :value="patient.id"
                                :data-dni="patient.dni"
                                :data-first-name="patient.first_name"
                                :data-last-name="patient.last_name"
                                x-text="patient.last_name + ', ' + patient.first_name + ' - DNI: ' + patient.dni"></option>
                    </template>
                </select>
            </div>

            <!-- Fecha, Monto y Consultorio en la misma línea -->
            <div class="grid grid-cols-3 gap-3">
                <!-- Fecha -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha *</label>
                    <input x-model="urgencyForm.appointment_date"
                           type="date"
                           :min="new Date().toISOString().split('T')[0]"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                           required>
                </div>

                <!-- Monto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                        <input x-model="urgencyForm.estimated_amount"
                               type="number"
                               step="0.01"
                               min="0"
                               value="0"
                               placeholder="0.00"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                               required>
                    </div>
                </div>

                <!-- Consultorio (opcional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Consultorio <span class="text-gray-400">(opcional)</span></label>
                    <select x-model="urgencyForm.office_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sin consultorio</option>
                        <template x-for="office in offices" :key="office.id">
                            <option :value="office.id" x-text="office.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Notas / Concepto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto / Notas</label>
                <textarea x-model="urgencyForm.notes"
                          rows="2"
                          placeholder="Motivo de la urgencia o notas adicionales..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </form>

        <!-- Footer -->
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-600">
            <button type="button"
                    @click="urgencyModalOpen = false"
                    :disabled="urgencyLoading"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50">
                Cancelar
            </button>
            <button @click="submitUrgencyForm()"
                    :disabled="urgencyLoading"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50">
                <svg x-show="urgencyLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Registrar Urgencia</span>
            </button>
        </div>
    </div>
</div>
