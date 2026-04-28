<div x-show="modalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     @click.self="closeModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-xl w-full p-6"
         @keydown.escape.window="closeModal()">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Editar gasto externo' : 'Nuevo gasto externo'"></h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">No afecta el balance de la caja diaria.</p>
            </div>
            <button type="button" @click="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitForm()" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                    <input type="date" x-model="form.expense_date" required
                           @input="clearError('expense_date')"
                           :class="hasError('expense_date') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500'"
                           class="w-full px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                    <p x-show="hasError('expense_date')" class="text-xs text-red-600 mt-1" x-text="getError('expense_date')"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto</label>
                    <input type="number" step="0.01" min="0.01" x-model="form.amount" required
                           @input="clearError('amount')"
                           :class="hasError('amount') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500'"
                           class="w-full px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                           placeholder="0.00">
                    <p x-show="hasError('amount')" class="text-xs text-red-600 mt-1" x-text="getError('amount')"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select x-model="form.movement_type_id" required
                            @change="clearError('movement_type_id')"
                            :class="hasError('movement_type_id') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500'"
                            class="w-full px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        <option value="">Seleccionar...</option>
                        @foreach($expenseTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->icon }} {{ $type->name }}</option>
                        @endforeach
                    </select>
                    <p x-show="hasError('movement_type_id')" class="text-xs text-red-600 mt-1" x-text="getError('movement_type_id')"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Medio de pago (opcional)</label>
                    <select x-model="form.payment_method"
                            @change="clearError('payment_method')"
                            :class="hasError('payment_method') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500'"
                            class="w-full px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        <option value="">—</option>
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="debit_card">Tarjeta Debito</option>
                        <option value="credit_card">Tarjeta Credito</option>
                        <option value="qr">QR</option>
                    </select>
                    <p x-show="hasError('payment_method')" class="text-xs text-red-600 mt-1" x-text="getError('payment_method')"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripcion</label>
                <input type="text" x-model="form.description" required maxlength="500"
                       @input="clearError('description')"
                       :class="hasError('description') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500'"
                       class="w-full px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                       placeholder="Ej: Sueldo administrativo">
                <p x-show="hasError('description')" class="text-xs text-red-600 mt-1" x-text="getError('description')"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas (opcional)</label>
                <textarea x-model="form.notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"
                          placeholder="Observaciones..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comprobante (opcional)</label>
                <template x-if="editing && form.receipt_url">
                    <div class="mb-2">
                        <a :href="form.receipt_url" target="_blank"
                           class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 text-xs font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v2.625a3.375 3.375 0 01-3.375 3.375h-8.25A3.375 3.375 0 014.5 16.875V14.25m7.5-10.5v11.25m0 0l-3-3m3 3l3-3" />
                            </svg>
                            Ver comprobante actual
                        </a>
                    </div>
                </template>
                <input type="file" @change="handleFile($event)" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">JPG/PNG/PDF hasta 4 MB.</p>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div>
                    <button type="button" x-show="editing" @click="confirmDelete()"
                            class="inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Eliminar
                    </button>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="closeModal()"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-sm">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="loading"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-md text-sm disabled:cursor-not-allowed">
                        <span x-show="!loading" x-text="editing ? 'Guardar' : 'Crear'"></span>
                        <span x-show="loading" x-text="editing ? 'Guardando...' : 'Creando...'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
