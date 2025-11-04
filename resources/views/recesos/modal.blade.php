<!-- Modal de Feriado -->
<div x-show="modalOpen"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     style="display: none;">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"
             @click="modalOpen = false"></div>

        <!-- Modal Content -->
        <div @click.stop
             class="relative z-10 w-full max-w-lg rounded-lg bg-white dark:bg-gray-800 shadow-xl">

            <!-- Header -->
            <div class="bg-emerald-50 dark:bg-emerald-950/20 px-6 py-4 border-b border-emerald-200/50 dark:border-emerald-800/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingHoliday ? 'Editar Feriado' : 'Nuevo Feriado'"></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Complete los datos del feriado</p>
                        </div>
                    </div>
                    <button @click="modalOpen = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm()">
                <div class="px-6 py-4 space-y-4">
                    <!-- Fecha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               x-model="form.exception_date"
                               required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="form.reason"
                               required
                               maxlength="255"
                               placeholder="Ej: Día del Trabajador"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600 flex justify-end gap-3">
                    <button type="button"
                            @click="modalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading" x-text="editingHoliday ? 'Actualizar' : 'Crear'"></span>
                        <span x-show="loading">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
