@extends('layouts.app')

@section('title', 'Editar Pago #' . $payment->receipt_number . ' - ' . config('app.name'))
@section('mobileTitle', 'Editar Pago')

@section('content')
<div class="p-6" x-data="editPaymentForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('payments.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Pagos</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('payments.show', $payment) }}" class="hover:text-gray-700 dark:hover:text-gray-200">Pago #{{ $payment->receipt_number }}</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Editar</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Pago</h1>
            <p class="text-gray-600 dark:text-gray-400">Modifique la información del pago #{{ $payment->receipt_number }}</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('payments.show', $payment) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Cancelar
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Información del Pago -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Editar Información del Pago
                </h2>

                <!-- Información no editable -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <label class="block font-medium text-gray-600 dark:text-gray-400">Paciente</label>
                            <div class="text-gray-900 dark:text-white">{{ $payment->patient->full_name }}</div>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-600 dark:text-gray-400">Tipo de Pago</label>
                            @php
                                $typeLabels = [
                                    'single' => 'Pago Individual',
                                    'package' => 'Paquete de Sesiones',
                                    'refund' => 'Reembolso'
                                ];
                            @endphp
                            <div class="text-gray-900 dark:text-white">{{ $typeLabels[$payment->payment_type] }}</div>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-600 dark:text-gray-400">N° Recibo</label>
                            <div class="font-mono text-gray-900 dark:text-white">{{ $payment->receipt_number }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método de Pago *</label>
                        <select x-model="form.payment_method" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="">Seleccionar método</option>
                            <option value="cash">💵 Efectivo</option>
                            <option value="transfer">🏦 Transferencia</option>
                            <option value="debit_card">💳 Tarjeta de Débito</option>
                            <option value="credit_card">💳 Tarjeta de Crédito</option>
                            <option value="qr">📱 QR</option>
                        </select>
                    </div>

                    <!-- Monto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                            <input x-model="form.amount" 
                                   type="number" 
                                   step="0.01" 
                                   min="0"
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>
                    </div>

                    <!-- Estado de Liquidación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado de Liquidación *</label>
                        <select x-model="form.liquidation_status" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="pending">Pendiente</option>
                            <option value="liquidated">Liquidado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                </div>

                <!-- Concepto -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto</label>
                    <textarea x-model="form.concept" 
                              rows="3"
                              placeholder="Descripción adicional del pago (opcional)"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- Turnos Asociados (solo informativo) -->
        @if($payment->paymentAppointments->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 715.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                        </svg>
                        Turnos Asociados (Solo lectura)
                    </h2>

                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            Los turnos asociados no pueden modificarse desde esta vista. Use la gestión individual de turnos si necesita realizar cambios.
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach($payment->paymentAppointments as $paymentAppointment)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $paymentAppointment->appointment->appointment_date->format('d/m/Y') }}
                                            - Dr. {{ $paymentAppointment->appointment->professional->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $paymentAppointment->appointment->appointment_date->format('H:i') }}
                                            @if($paymentAppointment->appointment->office)
                                                - {{ $paymentAppointment->appointment->office->name }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-green-600 dark:text-green-400">
                                            ${{ number_format($paymentAppointment->allocated_amount, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Botones -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('payments.show', $payment) }}" 
               class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="loading"
                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:cursor-not-allowed">
                <span x-show="!loading">Actualizar Pago</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Actualizando...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
function editPaymentForm() {
    return {
        loading: false,
        
        form: {
            payment_method: '{{ $payment->payment_method }}',
            amount: '{{ $payment->amount }}',
            concept: '{{ $payment->concept }}',
            liquidation_status: '{{ $payment->liquidation_status }}'
        },

        async submitForm() {
            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('payment_method', this.form.payment_method);
                formData.append('amount', this.form.amount);
                formData.append('concept', this.form.concept);
                formData.append('liquidation_status', this.form.liquidation_status);
                formData.append('_method', 'PUT');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('/payments/{{ $payment->id }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = `/payments/{{ $payment->id }}`;
                } else {
                    window.showToast(result.message || 'Error al actualizar el pago', 'error');
                }
            } catch (error) {
                window.showToast('Error al actualizar el pago', 'error');
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