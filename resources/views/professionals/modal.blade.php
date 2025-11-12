<!-- Modal -->
<div x-show="modalOpen"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto">
    
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="modalOpen = false"></div>
    
    <!-- Modal content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[85vh] overflow-y-auto">

            <!-- Header -->
            <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <svg x-show="!editingProfessional" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <svg x-show="editingProfessional" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingProfessional ? 'Editar Profesional' : 'Nuevo Profesional'"></h3>
                </div>
                <button @click="modalOpen = false" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <form @submit.prevent="submitForm()">
                <div class="px-6 py-3 space-y-3">

                    <!-- Información Personal -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información Personal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                                <input x-model="form.first_name"
                                       @input="form.first_name = form.first_name.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                                       type="text" required placeholder="Juan"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apellido *</label>
                                <input x-model="form.last_name"
                                       @input="form.last_name = form.last_name.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                                       type="text" required placeholder="Pérez"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">DNI *</label>
                                <input x-model="form.dni"
                                       @input="form.dni = form.dni.replace(/[^0-9.]/g, '')"
                                       type="text" required placeholder="12.345.678"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Matrícula</label>
                                <input x-model="form.license_number"
                                       type="text" placeholder="MP 12345"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información de Contacto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input x-model="form.email" type="email" placeholder="juan.perez@ejemplo.com"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                <input x-model="form.phone" type="tel" placeholder="+54 11 1234-5678"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Información Profesional -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información Profesional</h3>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <!-- Especialidad con botón (ocupa 6 columnas) -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Especialidad *</label>
                                <div class="flex gap-2">
                                    <select x-model="form.specialty_id" required
                                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Seleccionar...</option>
                                        <template x-for="specialty in specialties" :key="specialty.id">
                                            <option :value="specialty.id" x-text="specialty.name"></option>
                                        </template>
                                    </select>
                                    <button @click="openSpecialtyModal()" type="button"
                                            class="px-3 py-2 border border-emerald-600 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-md transition-colors"
                                            title="Crear nueva especialidad">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Comisión (ocupa 3 columnas) -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comisión (%) *</label>
                                <input x-model="form.commission_percentage" type="number" min="0" max="100" step="0.01" required placeholder="50.00"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <!-- Estado (ocupa 3 columnas) -->
                            <div x-show="editingProfessional" class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                <select x-model="form.is_active"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                                    <option value="true">Activo</option>
                                    <option value="false">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Pagos -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Configuración de Pagos</h3>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox"
                                       x-model="form.receives_transfers_directly"
                                       class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        Recibe transferencias directamente
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        Si está marcado, cuando un paciente paga con transferencia, el dinero se considera recibido por el profesional directamente (no entra a caja del centro). Solo se liquidará la comisión sobre efectivo y tarjetas.
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Notas Adicionales</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                            <textarea x-model="form.notes" rows="3" placeholder="Información adicional sobre el profesional..."
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Máximo 1000 caracteres</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="modalOpen = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-md min-w-[140px]">
                        <span x-show="!loading" x-text="editingProfessional ? 'Actualizar Profesional' : 'Crear Profesional'"></span>
                        <span x-show="loading">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Nueva Especialidad -->
<div x-show="specialtyModalOpen"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-60 overflow-y-auto">
    
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="specialtyModalOpen = false"></div>
    
    <!-- Modal content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            
            <!-- Header -->
            <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nueva Especialidad</h3>
                <button @click="specialtyModalOpen = false" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <form @submit.prevent="submitSpecialtyForm()">
                <div class="px-6 py-3 space-y-3">
                    <!-- Nombre de la especialidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de la especialidad</label>
                        <input x-model="specialtyForm.name" type="text" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Ej: Cardiología, Dermatología...">
                    </div>
                    
                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                        <textarea x-model="specialtyForm.description" required rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white"
                                  placeholder="Breve descripción de la especialidad..."></textarea>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button @click="specialtyModalOpen = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="specialtyLoading" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-md">
                        <span x-show="!specialtyLoading">Crear Especialidad</span>
                        <span x-show="specialtyLoading">Creando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>