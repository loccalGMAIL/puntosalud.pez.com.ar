<!-- Modal de Turno -->
<div x-show="modalOpen" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
    
    <!-- Modal Content -->
    <div @click.away="modalOpen = false" 
         class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                    </svg>
                    <span x-text="editingAppointment ? 'Editar Turno' : 'Nuevo Turno'"></span>
                </h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="editingAppointment ? 'Modifica los datos del turno' : 'Programa un nuevo turno para el paciente'"></p>
        </div>

        <!-- Body -->
        <form @submit.prevent="submitForm()" class="p-6 space-y-4">
            <!-- Profesional -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profesional *</label>
                <select x-model="form.professional_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                        required>
                    <option value="">Seleccionar profesional...</option>
                    <template x-for="professional in professionals" :key="professional.id">
                        <option :value="professional.id" x-text="'Dr. ' + professional.first_name + ' ' + professional.last_name + ' - ' + professional.specialty.name"></option>
                    </template>
                </select>
            </div>

            <!-- Paciente -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paciente *</label>
                <select x-model="form.patient_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                        required>
                    <option value="">Seleccionar paciente...</option>
                    <template x-for="patient in patients" :key="patient.id">
                        <option :value="patient.id" x-text="patient.last_name + ', ' + patient.first_name + ' - ' + patient.dni"></option>
                    </template>
                </select>
            </div>

            <!-- Fecha y Duración -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha *</label>
                    <input x-model="form.appointment_date" 
                           type="date" 
                           :min="new Date().toISOString().split('T')[0]"
                           @change="validateDateTime()"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duración *</label>
                    <select x-model="form.duration" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                            required>
                        <option value="15">15 minutos</option>
                        <option value="30">30 minutos</option>
                        <option value="45">45 minutos</option>
                        <option value="60">1 hora</option>
                        <option value="90">1 hora 30 min</option>
                        <option value="120">2 horas</option>
                    </select>
                </div>
            </div>

            <!-- Horario -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horario *</label>
                <input x-model="form.appointment_time" 
                       type="time" 
                       min="08:00" 
                       max="18:00"
                       step="900"
                       @change="validateDateTime()"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                       required>
                <div x-show="pastTimeError" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="pastTimeError"></div>
            </div>

            <!-- Consultorio y Monto -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Consultorio <span class="text-gray-400">(opcional)</span></label>
                    <select x-model="form.office_id" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sin consultorio</option>
                        <template x-for="office in offices" :key="office.id">
                            <option :value="office.id" x-text="office.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto Estimado <span class="text-gray-400">(opcional)</span></label>
                    <input x-model="form.estimated_amount" 
                           type="number" 
                           step="0.01" 
                           min="0" 
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Estado (solo para edición) -->
            <div x-show="editingAppointment">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                <select x-model="form.status" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    <option value="scheduled">Programado</option>
                    <option value="attended">Atendido</option>
                    <option value="cancelled">Cancelado</option>
                    <option value="absent">Ausente</option>
                </select>
            </div>

            <!-- Notas -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas <span class="text-gray-400">(opcional)</span></label>
                <textarea x-model="form.notes" 
                          rows="3" 
                          placeholder="Notas adicionales sobre el turno..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            <!-- Información -->
            <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
                <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span><strong>Horario laboral:</strong> De 8:00 a 18:00, lunes a viernes.</span>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-600">
            <button type="button" 
                    @click="modalOpen = false"
                    :disabled="loading"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50">
                Cancelar
            </button>
            <button @click="submitForm()"
                    :disabled="loading"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50">
                <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="editingAppointment ? 'Actualizar' : 'Crear'"></span> Turno
            </button>
        </div>
    </div>
</div>