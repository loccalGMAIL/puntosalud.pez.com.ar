@extends('layouts.app')


@section('title', 'Todas las Consultas - ' . config('app.name'))
@section('mobileTitle', 'Consultas del Día')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Todas las Consultas</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Todas las Consultas del Día</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $data['fecha'] }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Lista de consultas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <div class="space-y-3">
                @forelse($data['consultasDetalle'] as $consulta)
                    <div class="group flex items-center justify-between p-4 rounded-lg border bg-white/80 transition-all duration-200 hover:shadow-md dark:bg-gray-800/50
                        @if($consulta['status'] === 'attended') border-emerald-100 hover:border-emerald-200 dark:border-emerald-800/30 dark:hover:border-emerald-700/50
                        @elseif($consulta['status'] === 'scheduled') border-blue-100 hover:border-blue-200 dark:border-blue-800/30 dark:hover:border-blue-700/50
                        @elseif($consulta['status'] === 'cancelled') border-red-100 hover:border-red-200 dark:border-red-800/30 dark:hover:border-red-700/50
                        @else border-amber-100 hover:border-amber-200 dark:border-amber-800/30 dark:hover:border-amber-700/50 @endif">

                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full transition-transform duration-200 group-hover:scale-110
                                @if($consulta['status'] === 'attended') bg-emerald-100 dark:bg-emerald-900/30
                                @elseif($consulta['status'] === 'scheduled') bg-blue-100 dark:bg-blue-900/30
                                @elseif($consulta['status'] === 'cancelled') bg-red-100 dark:bg-red-900/30
                                @else bg-amber-100 dark:bg-amber-900/30 @endif">

                                @if($consulta['status'] === 'attended')
                                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif($consulta['status'] === 'scheduled')
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif($consulta['status'] === 'cancelled')
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $consulta['paciente'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $consulta['profesional'] }} • {{ $consulta['hora'] }}</p>
                            </div>
                        </div>
                        <div class="text-right flex flex-col items-end gap-2">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">${{ number_format($consulta['monto'], 0, ',', '.') }}</p>
                                    <div class="flex items-center gap-1">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            @if($consulta['status'] === 'attended') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                            @elseif($consulta['status'] === 'scheduled') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                            @elseif($consulta['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                            @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                            {{ $consulta['statusLabel'] }}
                                        </span>
                                        @if($consulta['isPaid'])
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                💰 Pagado
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="flex gap-1" x-data="appointmentActions({{ $consulta['id'] }}, {{ $consulta['monto'] ?? 0 }})">
                                    @if($consulta['isPaid'])
                                        <a href="{{ route('payments.show', $consulta['paymentId']) }}"
                                           class="p-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition-colors duration-200"
                                           title="Ver detalle del pago">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('payments.print-receipt', $consulta['paymentId']) }}?print=1"
                                           target="_blank"
                                           class="p-1.5 text-xs bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200"
                                           title="Imprimir recibo">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if($consulta['canMarkAttended'])
                                        <button @click="markAttended()" :disabled="loading"
                                                class="p-1.5 text-xs bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                title="Marcar como atendido">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if($consulta['canMarkCompleted'])
                                        <button @click="markCompletedAndPaid()" :disabled="loading"
                                                class="p-1.5 text-xs bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                title="Finalizar y cobrar">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if($consulta['status'] === 'scheduled')
                                        <button @click="markAbsent()" :disabled="loading"
                                                class="p-1.5 text-xs bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                title="Marcar como ausente">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 3v2M18 3v2M3 18V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No hay consultas para hoy</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal para finalizar y cobrar -->
<x-payment-modal />

<!-- Modal del sistema para notificaciones y confirmaciones -->
<x-system-modal />

<script>
// Dashboard Management - Modern ES6+ approach
const DashboardAPI = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),

    async makeRequest(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            ...options
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Error en la operación');
        }

        return result;
    },

    async showNotification(message, type = 'info') {
        const modalType = type === 'error' ? 'error' : type === 'success' ? 'success' : 'confirm';
        const title = type === 'error' ? 'Error' : type === 'success' ? 'Éxito' : 'Información';
        await SystemModal.show(modalType, title, message, 'Aceptar', false);
    },

    reloadPage(delay = 500) {
        setTimeout(() => location.reload(), delay);
    }
};

// Global modal reference
let globalPaymentModal = null;

// Alpine.js appointment actions component
function appointmentActions(appointmentId, estimatedAmount = 0) {
    return {
        loading: false,

        async markAttended() {
            if (this.loading) return;
            this.loading = true;

            try {
                await DashboardAPI.makeRequest(`/dashboard/appointments/${appointmentId}/mark-attended`, {
                    method: 'POST'
                });

                await DashboardAPI.showNotification('Turno marcado como atendido exitosamente', 'success');
                DashboardAPI.reloadPage();
            } catch (error) {
                await DashboardAPI.showNotification(error.message, 'error');
                this.loading = false;
            }
        },

        markCompletedAndPaid() {
            globalPaymentModal?.showModal(appointmentId, estimatedAmount);
        },

        async markAbsent() {
            const confirmed = await SystemModal.confirm(
                'Confirmar ausencia',
                '¿Está seguro de marcar este turno como ausente?',
                'Sí, marcar ausente',
                'Cancelar'
            );

            if (!confirmed) return;
            if (this.loading) return;

            this.loading = true;

            try {
                await DashboardAPI.makeRequest(`/dashboard/appointments/${appointmentId}/mark-absent`, {
                    method: 'POST'
                });

                await DashboardAPI.showNotification('Turno marcado como ausente', 'success');
                DashboardAPI.reloadPage();
            } catch (error) {
                await DashboardAPI.showNotification(error.message, 'error');
                this.loading = false;
            }
        }
    };
}

// Alpine.js payment modal component
function paymentModal() {
    return {
        show: false,
        loading: false,
        currentAppointmentId: null,
        paymentForm: { final_amount: '', payment_method: '', concept: '' },

        init() {
            globalPaymentModal = this;
        },

        showModal(appointmentId, estimatedAmount = 0) {
            this.currentAppointmentId = appointmentId;
            this.paymentForm = {
                final_amount: estimatedAmount || '',
                payment_method: '',
                concept: ''
            };
            this.show = true;
        },

        hide() {
            this.show = false;
            this.currentAppointmentId = null;
            this.loading = false;
        },

        async submitPayment() {
            if (this.loading) return;

            // Validation
            if (!this.paymentForm.final_amount || !this.paymentForm.payment_method) {
                await DashboardAPI.showNotification('Complete todos los campos requeridos', 'error');
                return;
            }

            this.loading = true;

            try {
                const result = await DashboardAPI.makeRequest(
                    `/dashboard/appointments/${this.currentAppointmentId}/mark-completed-paid`,
                    {
                        method: 'POST',
                        body: JSON.stringify(this.paymentForm)
                    }
                );

                // Cerrar el modal de pago primero
                this.hide();

                // Preguntar si desea imprimir el recibo
                if (result.payment_id) {
                    const printReceipt = await SystemModal.confirm(
                        'Imprimir recibo',
                        '¿Desea imprimir el recibo ahora?',
                        'Sí, imprimir',
                        'No'
                    );

                    if (printReceipt) {
                        window.open(`/payments/${result.payment_id}/print-receipt?print=1`, '_blank');
                    }
                }

                DashboardAPI.reloadPage();
            } catch (error) {
                await DashboardAPI.showNotification(error.message, 'error');
                this.loading = false;
            }
        }
    };
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection
