@extends('layouts.app')

@section('title', 'Retiro de Caja - ' . config('app.name'))
@section('mobileTitle', 'Retiro de Caja')

@section('content')
<div class="p-4 sm:p-6" x-data="withdrawalForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('cash.daily') }}" class="hover:text-gray-700">Caja del Día</a>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Retiro de Caja</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">Retiro de Caja</h1>
        </div>

        <a href="{{ route('cash.daily') }}"
           class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 self-start sm:self-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Volver a Caja
        </a>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submitWithdrawal()" class="space-y-3">

        <!-- Campos principales -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Monto -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto a Retirar *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number"
                                   x-model="form.amount"
                                   step="0.01"
                                   min="0.01"
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-base font-semibold"
                                   placeholder="0.00"
                                   required>
                        </div>
                        <div x-show="availableBalance !== null" class="mt-1 text-xs text-gray-500">
                            Saldo disponible: $<span x-text="formatMoney(availableBalance)"></span>
                        </div>
                    </div>

                    <!-- Tipo de Retiro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Retiro *</label>
                        <select x-model="form.withdrawal_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                required>
                            <option value="">Seleccionar tipo</option>
                            @foreach($withdrawalTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago *</label>
                        <select x-model="form.payment_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                required>
                            <option value="">Seleccionar método</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="debit_card">Tarjeta de Débito</option>
                            <option value="credit_card">Tarjeta de Crédito</option>
                            <option value="qr">QR / Mercado Pago</option>
                        </select>
                    </div>

                    <!-- Destinatario -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Destinatario (opcional)</label>
                        <input type="text"
                               x-model="form.recipient"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Nombre del destinatario">
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
                        <input type="text"
                               x-model="form.description"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Motivo del retiro"
                               required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acordeón: Notas adicionales -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <button type="button"
                    @click="extrasOpen = !extrasOpen"
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors duration-150">
                <span class="flex items-center gap-2 text-sm font-medium text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                    Notas adicionales
                    <span x-show="form.notes.length > 0"
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                        con datos
                    </span>
                </span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                     :class="extrasOpen ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="extrasOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="border-t border-gray-100 px-4 py-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                <textarea x-model="form.notes"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none text-sm"
                          rows="2"
                          placeholder="Información adicional sobre el retiro..."></textarea>
            </div>
        </div>

        <!-- Alerta de confirmación -->
        <div x-show="form.amount > 0"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 flex items-start gap-3">
            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p class="text-sm text-amber-700">
                Se registrará un retiro de <strong>$<span x-text="formatMoney(form.amount)"></span></strong>.
                Asegúrese de que el efectivo físico haya sido retirado antes de confirmar.
            </p>
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-1">
            <button type="button"
                    @click="window.location.href = '{{ route('cash.daily') }}'"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </button>
            <button type="submit"
                    :disabled="loading || !form.amount || !form.payment_method || !form.withdrawal_type || !form.description"
                    class="flex-1 px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-lg transition-colors disabled:cursor-not-allowed">
                <span x-show="!loading">Registrar Retiro</span>
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
function withdrawalForm() {
    return {
        loading: false,
        availableBalance: null,
        extrasOpen: false,

        form: {
            amount: '',
            payment_method: '',
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
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

            if (!this.form.amount || !this.form.payment_method || !this.form.withdrawal_type || !this.form.description) {
                window.showToast('Complete todos los campos requeridos', 'error');
                return;
            }

            if (parseFloat(this.form.amount) <= 0) {
                window.showToast('El monto debe ser mayor a 0', 'error');
                return;
            }

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
                    window.showToast(result.message, 'success');
                    setTimeout(() => { window.location.href = '/cash/daily'; }, 1000);
                } else {
                    window.showToast(result.message, 'error');
                }
            } catch (error) {
                window.showToast('Error al procesar el retiro: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@endsection
