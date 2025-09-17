@extends('layouts.app')

@section('title', 'Retiro de Caja - ' . config('app.name'))
@section('mobileTitle', 'Retiro de Caja')

@section('content')
<div class="p-6" x-data="withdrawalForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('cash.daily') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Caja del Día</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Retiro de Caja</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Retiro de Caja</h1>
            <p class="text-gray-600 dark:text-gray-400">Registrar salida de efectivo de la caja</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('cash.daily') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Volver a Caja
            </a>
        </div>
    </div>

    <!-- Formulario de Retiro -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <form @submit.prevent="submitWithdrawal()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Monto -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Monto a Retirar *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500 dark:text-gray-400">$</span>
                            <input type="number"
                                   x-model="form.amount"
                                   step="0.01"
                                   min="0.01"
                                   class="w-full pl-8 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white text-lg font-semibold"
                                   placeholder="0.00"
                                   required>
                        </div>
                        <div x-show="availableBalance !== null" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Saldo disponible: $<span x-text="formatMoney(availableBalance)"></span>
                        </div>
                    </div>

                    <!-- Tipo de Retiro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Retiro *
                        </label>
                        <select x-model="form.withdrawal_type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="">Seleccionar tipo</option>
                            @foreach($withdrawalTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Destinatario -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Destinatario (opcional)
                        </label>
                        <input type="text"
                               x-model="form.recipient"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Nombre del destinatario">
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descripción del Retiro *
                        </label>
                        <input type="text"
                               x-model="form.description"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Descripción del motivo del retiro"
                               required>
                    </div>

                    <!-- Notas Adicionales -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notas Adicionales (opcional)
                        </label>
                        <textarea x-model="form.notes"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                                  rows="3"
                                  placeholder="Información adicional sobre el retiro..."></textarea>
                    </div>
                </div>

                <!-- Alerta de Confirmación -->
                <div x-show="form.amount > 0" class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-1 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Confirmar Retiro</h3>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                Se registrará un retiro de $<span x-text="formatMoney(form.amount)"></span> de la caja.
                                Asegúrese de que el efectivo físico haya sido retirado antes de confirmar.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button"
                            @click="window.location.href = '{{ route('cash.daily') }}'"
                            class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="loading || !form.amount || !form.withdrawal_type || !form.description"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-lg transition-colors disabled:cursor-not-allowed">
                        <span x-show="!loading">Registrar Retiro</span>
                        <span x-show="loading">Procesando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function withdrawalForm() {
    return {
        loading: false,
        availableBalance: null,

        form: {
            amount: '',
            withdrawal_type: '',
            description: '',
            recipient: '',
            notes: ''
        },

        init() {
            this.getAvailableBalance();
        },

        async getAvailableBalance() {
            try {
                const response = await fetch('/cash/status', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    // Aquí necesitaríamos el saldo actual, por ahora usamos un placeholder
                    this.availableBalance = 0; // Se actualizará con el endpoint real
                }
            } catch (error) {
                console.error('Error al obtener saldo disponible:', error);
            }
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount || 0);
        },

        async submitWithdrawal() {
            if (this.loading) return;

            // Validaciones básicas
            if (!this.form.amount || !this.form.withdrawal_type || !this.form.description) {
                this.showNotification('Complete todos los campos requeridos', 'error');
                return;
            }

            if (parseFloat(this.form.amount) <= 0) {
                this.showNotification('El monto debe ser mayor a 0', 'error');
                return;
            }

            // Confirmar el retiro
            if (!confirm(`¿Está seguro de registrar un retiro de $${this.formatMoney(this.form.amount)}?`)) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch('/cash/withdrawal', {
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
                    this.showNotification(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = '/cash/daily';
                    }, 1000);
                } else {
                    this.showNotification(result.message, 'error');
                }

            } catch (error) {
                this.showNotification('Error al procesar el retiro: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        showNotification(message, type = 'info') {
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
            alert(`${icon} ${message}`);
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@endsection