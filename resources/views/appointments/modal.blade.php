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
                        <option value="20">20 minutos</option>
                        <option value="30">30 minutos</option>
                        <option value="40">40 minutos</option>
                        <option value="45">45 minutos</option>
                        <option value="60">1 hora</option>
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

            <!-- Sección de Pago -->
            <div x-show="!editingAppointment" class="border-t border-gray-200 dark:border-gray-600 pt-4">
                <div class="flex items-center gap-3 mb-4">
                    <input x-model="form.pay_now" 
                           type="checkbox" 
                           id="pay_now"
                           class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="pay_now" class="text-sm font-medium text-gray-700 dark:text-gray-300">💰 Cobrar turno ahora</label>
                </div>
                
                <!-- Tipo de Pago -->
                <div x-show="form.pay_now" x-transition class="mb-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                               :class="form.payment_type === 'single' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : ''">
                            <input type="radio" 
                                   x-model="form.payment_type" 
                                   value="single" 
                                   class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">🎯 Pago Individual</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Un turno, un pago</div>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                               :class="form.payment_type === 'package' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : ''">
                            <input type="radio" 
                                   x-model="form.payment_type" 
                                   value="package" 
                                   class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">📦 Paquete/Tratamiento</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Múltiples turnos, un pago</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Formulario Pago Individual -->
                <div x-show="form.pay_now && form.payment_type === 'single'" x-transition class="space-y-4 bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-lg border border-emerald-200 dark:border-emerald-800">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto del Turno *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                                <input x-model="form.payment_amount" 
                                       type="number" 
                                       step="0.01" 
                                       min="0"
                                       placeholder="0.00"
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                                       :required="form.pay_now">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método de Pago *</label>
                            <select x-model="form.payment_method" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                                    :required="form.pay_now">
                                <option value="">Seleccionar...</option>
                                <option value="cash">💵 Efectivo</option>
                                <option value="transfer">🏦 Transferencia</option>
                                <option value="card">💳 Tarjeta</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto del Pago</label>
                        <input x-model="form.payment_concept" 
                               type="text" 
                               placeholder="Pago individual consulta..."
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <!-- Formulario Pago Paquete -->
                <div x-show="form.pay_now && form.payment_type === 'package'" x-transition class="space-y-4 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sesiones del Paquete *</label>
                            <input x-model="form.package_sessions" 
                                   type="number" 
                                   min="2" 
                                   max="20"
                                   placeholder="6"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   :required="form.pay_now && form.payment_type === 'package'"
                                   @input="calculatePackageTotal()">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio por Sesión *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                                <input x-model="form.session_price" 
                                       type="number" 
                                       step="0.01" 
                                       min="0"
                                       placeholder="3000"
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                       :required="form.pay_now && form.payment_type === 'package'"
                                       @input="calculatePackageTotal()">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total del Paquete</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                                <input x-model="form.payment_amount" 
                                       type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-gray-50 dark:bg-gray-600"
                                       readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método de Pago *</label>
                        <select x-model="form.payment_method" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                :required="form.pay_now">
                            <option value="">Seleccionar...</option>
                            <option value="cash">💵 Efectivo</option>
                            <option value="transfer">🏦 Transferencia</option>
                            <option value="card">💳 Tarjeta</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto del Tratamiento</label>
                        <input x-model="form.payment_concept" 
                               type="text" 
                               placeholder="Ej: Paquete 6 sesiones kinesiología..."
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="bg-amber-50 dark:bg-amber-900/20 p-3 rounded-lg border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-2 text-sm text-amber-800 dark:text-amber-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span><strong>Paquete:</strong> Solo se crea ESTE turno. Los turnos adicionales deberás crearlos por separado (se asignarán automáticamente al paquete).</span>
                        </div>
                    </div>
                </div>
                
                <!-- Información general -->
                <div x-show="form.pay_now" x-transition class="mt-4">
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-lg border border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center gap-2 text-sm text-emerald-800 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <span>El pago <strong>ingresará inmediatamente</strong> a caja y se liquidará al profesional cuando se presente.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información -->
            <div x-show="!form.pay_now" class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
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