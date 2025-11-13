<!-- Modal para finalizar y cobrar -->
<div x-data="paymentModal()" 
     x-show="show" 
     x-cloak
     class="modal-overlay fixed inset-0 z-[9999] flex items-center justify-center p-4"
     style="background-color: rgba(0, 0, 0, 0.5);"
     @click.self="hide()">
    
    <!-- Modal -->
    <div class="modal-content relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-md p-6"
         @click.stop
         x-transition:enter="transition-all ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition-all ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Finalizar y Cobrar Consulta
            </h3>
        </div>
        
        <!-- Form Content -->
        <form @submit.prevent="submitPayment()" class="mb-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Monto Final *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                        <input x-model="paymentForm.final_amount" 
                               type="number" 
                               step="0.01" 
                               min="0"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Método de Pago *
                    </label>
                    <select x-model="paymentForm.payment_method" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        <option value="">Seleccionar método</option>
                        <option value="cash">💵 Efectivo</option>
                        <option value="transfer">🏦 Transferencia</option>
                        <option value="debit_card">💳 Tarjeta de Débito</option>
                        <option value="credit_card">💳 Tarjeta de Crédito</option>
                        <option value="qr">📱 QR</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Concepto (Opcional)
                    </label>
                    <input x-model="paymentForm.concept" 
                           type="text" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Concepto del pago...">
                </div>
            </div>
        </form>
        
        <!-- Botones en la parte inferior del modal -->
        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
            <div class="flex gap-3">
                <button type="button" 
                        @click="hide()"
                        :disabled="loading"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancelar
                </button>
                <button type="button"
                        @click="submitPayment()"
                        :disabled="loading"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:bg-green-400 rounded-lg disabled:cursor-not-allowed transition-colors">
                    <span x-show="!loading">💰 Cobrar</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <span class="animate-spin">⏳</span>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>