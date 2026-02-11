@props([
    'theoreticalBalance' => 0,
    'incomeTotal' => 0,
    'expenseTotal' => 0,
    'closeDate' => null,
    'isUnclosedDate' => false
])

<!-- Modal para cerrar caja -->
<div x-data="cashCloseModal(@js($theoreticalBalance), @js($incomeTotal), @js($expenseTotal), @js($closeDate), @js($isUnclosedDate))"
     x-show="modalVisible"
     x-cloak
     class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
     @click.self="closeModal()"
     @close-cash-modal.window="openModal()">
    <div class="modal-content bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span x-text="isUnclosedDate ? 'Cerrar Caja del ' + formatShortDate(closeDate) : 'Cerrar Caja del Día'"></span>
            </h2>

            <form @submit.prevent="submitClose()">
                <div class="space-y-4">
                    <!-- Información de la caja -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                        <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">Resumen del Día</h3>
                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <div class="flex justify-between">
                                <span>Saldo teórico:</span>
                                <span class="font-medium" x-text="'$' + theoreticalBalance.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Ingresos del día:</span>
                                <span class="font-medium text-green-600" x-text="'+$' + incomeTotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Egresos del día:</span>
                                <span class="font-medium text-red-600" x-text="'-$' + expenseTotal.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Efectivo contado en caja *
                        </label>
                        <input type="number"
                               x-model="form.closing_amount"
                               step="0.01"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="0.00"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notas del cierre (opcional)
                        </label>
                        <textarea x-model="form.notes"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                  rows="2"
                                  placeholder="Observaciones sobre el cierre..."></textarea>
                    </div>

                    <!-- Alerta de diferencia -->
                    <div x-show="showDifference" x-cloak class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">Diferencia detectada</h4>
                                <p class="text-sm text-amber-700 dark:text-amber-300" x-text="differenceMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button"
                            @click="closeModal()"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="loading || !form.closing_amount"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white rounded-lg transition-colors">
                        <span x-show="!loading">Cerrar Caja</span>
                        <span x-show="loading">Cerrando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cashCloseModal(theoreticalBalance, incomeTotal, expenseTotal, closeDate, isUnclosedDate) {
    return {
        modalVisible: false,
        loading: false,
        theoreticalBalance: theoreticalBalance,
        incomeTotal: incomeTotal,
        expenseTotal: expenseTotal,
        closeDate: closeDate,
        isUnclosedDate: isUnclosedDate,

        form: {
            closing_amount: theoreticalBalance.toFixed(2),  // Pre-llenado
            notes: '',
            close_date: closeDate
        },

        get showDifference() {
            if (!this.form.closing_amount) return false;
            const counted = parseFloat(this.form.closing_amount);
            const theoretical = this.theoreticalBalance;
            return Math.abs(counted - theoretical) > 0.01;
        },

        get differenceMessage() {
            if (!this.form.closing_amount) return '';
            const counted = parseFloat(this.form.closing_amount);
            const theoretical = this.theoreticalBalance;
            const difference = counted - theoretical;

            if (difference > 0) {
                return `Sobrante de $${Math.abs(difference).toFixed(2)}`;
            } else if (difference < 0) {
                return `Faltante de $${Math.abs(difference).toFixed(2)}`;
            }
            return 'Sin diferencias';
        },

        openModal() {
            // Reset form con saldo teórico pre-llenado
            this.form = {
                closing_amount: this.theoreticalBalance.toFixed(2),
                notes: '',
                close_date: this.closeDate
            };
            this.modalVisible = true;
        },

        closeModal() {
            this.modalVisible = false;
            this.loading = false;
        },

        async submitClose() {
            if (this.loading) return;

            if (!this.form.closing_amount) {
                this.showNotification('Complete el monto contado', 'error');
                return;
            }

            // Confirmar si hay diferencia significativa
            const counted = parseFloat(this.form.closing_amount);
            const theoretical = this.theoreticalBalance;
            const difference = Math.abs(counted - theoretical);

            if (difference > 0.01) {
                const confirmMessage = difference > theoretical * 0.1 ?
                    `Se detectó una diferencia importante de $${difference.toFixed(2)}. ¿Está seguro de cerrar la caja?` :
                    `¿Confirmar cierre con diferencia de $${difference.toFixed(2)}?`;

                if (!confirm(confirmMessage)) return;
            } else {
                const dateMsg = this.isUnclosedDate ? ` del ${this.formatShortDate(this.closeDate)}` : ' del día';
                if (!confirm(`¿Está seguro de cerrar la caja${dateMsg}?`)) return;
            }

            this.loading = true;

            try {
                const response = await fetch('/cash/close', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {
                    this.closeModal();
                    this.showNotification('Caja cerrada exitosamente', 'success');

                    // Redirigir al reporte automáticamente
                    setTimeout(() => {
                        window.location.href = result.redirect_url;
                    }, 500);
                } else {
                    this.showNotification(result.message, 'error');
                }

            } catch (error) {
                this.showNotification('Error al cerrar la caja: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        formatShortDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-AR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        showNotification(message, type = 'info') {
            window.showToast(message, type);
        }
    }
}
</script>

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* Asegurar que el modal esté por encima de todo */
.modal-overlay {
    z-index: 10000 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
}

.modal-content {
    position: relative !important;
    z-index: 10001 !important;
}
</style>
@endpush
