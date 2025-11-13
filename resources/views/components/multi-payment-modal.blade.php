{{--
    Modal de cobro — versión congruente, limpia y alineada
--}}

@props([
    'title' => 'Registrar Cobro',
    'readonlyAmount' => false,
])

<div x-data="multiPaymentModal(@js($readonlyAmount))"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
     @click.self="hide()">

    <div class="relative bg-white dark:bg-gray-850 rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            <button @click="hide()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Cuerpo -->
        <form @submit.prevent="submitPayment()" class="p-6 grid grid-cols-2 gap-8">
            <!-- Columna izquierda -->
            <div class="flex flex-col justify-between space-y-6">
                <!-- Monto -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Monto Total *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 dark:text-gray-400">$</span>
                        <input x-model="totalAmount"
                               type="number"
                               step="0.01"
                               min="0"
                               :readonly="isReadonlyAmount"
                               @input="updateRemaining()"
                               class="w-full pl-7 pr-3 py-2.5 text-lg font-semibold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-750 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :class="isReadonlyAmount ? 'bg-gray-100 dark:bg-gray-900' : ''"
                               required>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 p-4 text-sm border border-blue-200 dark:border-blue-700">
                    <div class="flex justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Asignado:</span>
                        <span class="font-semibold text-blue-700 dark:text-blue-300">$<span x-text="assignedAmount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between mt-2 border-t border-blue-200 dark:border-blue-700 pt-2">
                        <span class="font-medium text-gray-900 dark:text-white">Restante:</span>
                        <span class="font-bold" :class="getBalanceClass()">
                            $<span x-text="remaining.toFixed(2)"></span>
                        </span>
                    </div>
                    <template x-if="remaining !== 0">
                        <p class="mt-2 text-xs" 
                           :class="remaining > 0 ? 'text-yellow-700 dark:text-yellow-400' : 'text-red-700 dark:text-red-400'">
                            <span x-show="remaining > 0">⚠️ Falta $<span x-text="remaining.toFixed(2)"></span></span>
                            <span x-show="remaining < 0">❌ Excede $<span x-text="Math.abs(remaining).toFixed(2)"></span></span>
                        </p>
                    </template>
                </div>

                <!-- Concepto -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Concepto</label>
                    <textarea x-model="concept"
                              rows="3"
                              class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-750 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                              placeholder="Ej: Pago parcial de turno médico..."></textarea>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="flex flex-col justify-start space-y-4">
                <!-- Botón agregar -->
                <button type="button"
                        @click="addPaymentMethod()"
                        class="self-end text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline mb-1">
                    + Agregar
                </button>

                <!-- Lista de métodos de pago -->
                <template x-for="(method, index) in paymentMethods" :key="index">
                    <div class="grid grid-cols-3 gap-3 p-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-750 items-center">
                        <!-- Tipo -->
                        <select x-model="method.type"
                                class="col-span-1 w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Método...</option>
                            <option value="cash">💵 Efectivo</option>
                            <option value="transfer">🏦 Transferencia</option>
                            <option value="debit_card">💳 Débito</option>
                            <option value="credit_card">💳 Crédito</option>
                        </select>

                        <!-- Monto -->
                        <div class="relative col-span-1">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input x-model="method.amount"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   @input="updateRemaining()"
                                   class="w-full pl-7 pr-3 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>

                        <!-- Eliminar -->
                        <div class="flex justify-end col-span-1">
                            <button type="button" 
                                    @click="removePaymentMethod(index)"
                                    class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Placeholder -->
                <div x-show="paymentMethods.length === 0"
                     class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl">
                    <svg class="w-12 h-12 mb-2 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                    <p class="text-sm">Sin formas de pago. Agregue una para comenzar.</p>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <button type="button"
                    @click="hide()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                Cancelar
            </button>
            <button type="button"
                    @click="submitPayment()"
                    :disabled="!canSubmit() || loading"
                    class="flex items-center justify-center gap-2 px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:bg-gray-400 rounded-lg transition-colors">
                <template x-if="!loading">
                    <span>💰 Confirmar Cobro</span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
                        </svg>
                        Procesando...
                    </span>
                </template>
            </button>
        </div>
    </div>
</div>


<script>
function multiPaymentModal(readonlyAmount = false) {
    return {
        show: false,
        loading: false,
        totalAmount: 0,
        concept: '',
        paymentMethods: [],
        assignedAmount: 0,
        remaining: 0,
        isReadonlyAmount: readonlyAmount,
        onSubmitCallback: null,

        open(amount = 0, callback = null) {
            this.totalAmount = amount;
            this.concept = '';
            this.paymentMethods = [];
            this.assignedAmount = 0;
            this.remaining = amount;
            this.loading = false;
            this.onSubmitCallback = callback;
            this.show = true;

            // Agregar una forma de pago por defecto con el monto completo solo si amount > 0
            if (parseFloat(amount) > 0) {
                this.addPaymentMethod();
            }
        },

        hide() {
            this.show = false;
        },

        addPaymentMethod() {
            this.paymentMethods.push({
                type: '',
                amount: this.remaining > 0 ? this.remaining : 0
            });
            this.updateRemaining();
        },

        removePaymentMethod(index) {
            this.paymentMethods.splice(index, 1);
            this.updateRemaining();
        },

        updateRemaining() {
            this.assignedAmount = this.paymentMethods.reduce((sum, method) => {
                return sum + parseFloat(method.amount || 0);
            }, 0);
            this.remaining = parseFloat(this.totalAmount || 0) - this.assignedAmount;
        },

        getBalanceClass() {
            if (Math.abs(this.remaining) < 0.01) {
                return 'text-green-600 dark:text-green-400';
            } else if (this.remaining > 0) {
                return 'text-yellow-600 dark:text-yellow-400';
            } else {
                return 'text-red-600 dark:text-red-400';
            }
        },

        canSubmit() {
            // Si es monto cero, permitir sin métodos de pago
            if (parseFloat(this.totalAmount) === 0) {
                return true;
            }

            if (this.paymentMethods.length === 0) return false;

            const allValid = this.paymentMethods.every(method => {
                return method.type && parseFloat(method.amount) >= 0;
            });
            if (!allValid) return false;

            if (Math.abs(this.remaining) > 0.01) return false;

            return true;
        },

        async submitPayment() {
            if (!this.canSubmit() || this.loading) return;

            this.loading = true;

            const data = {
                total_amount: parseFloat(this.totalAmount),
                concept: this.concept,
                payment_details: this.paymentMethods.map(method => ({
                    payment_method: method.type,
                    amount: parseFloat(method.amount)
                }))
            };

            try {
                if (this.onSubmitCallback && typeof this.onSubmitCallback === 'function') {
                    // Cerrar el modal ANTES de ejecutar el callback
                    this.hide();

                    // Pequeño delay para que el modal se cierre visualmente antes de mostrar el siguiente dialog
                    await new Promise(resolve => setTimeout(resolve, 100));

                    // Ahora ejecutar el callback (que mostrará la confirmación de impresión)
                    await this.onSubmitCallback(data);
                } else {
                    console.error('No callback provided for payment submission');
                }
            } catch (error) {
                console.error('Error submitting payment:', error);
                // Si hay error, el modal ya está cerrado, así que solo mostramos el error
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
