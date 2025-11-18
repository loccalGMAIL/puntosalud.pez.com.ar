@extends('layouts.app')

@section('title', 'Pago #' . $payment->receipt_number . ' - ' . config('app.name'))
@section('mobileTitle', 'Detalle Pago')

@section('content')
<div class="p-6" x-data="paymentDetail()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('payments.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Pagos</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Pago #{{ $payment->receipt_number }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalle del Pago</h1>
            <p class="text-gray-600 dark:text-gray-400">Información completa del pago registrado</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('payments.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver
            </a>
            
            {{-- Edición removida: usar retiros/ingresos manuales para correcciones --}}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Datos del Pago -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                        </svg>
                        Información del Pago
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Número de Recibo</label>
                            <div class="text-lg font-mono text-gray-900 dark:text-white">{{ $payment->receipt_number }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha y Hora</label>
                            <div class="text-lg text-gray-900 dark:text-white">
                                {{ $payment->payment_date->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tipo de Pago</label>
                            @php
                                $typeColors = [
                                    'single' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                    'package' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                    'package_purchase' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                    'refund' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                ];
                                $typeLabels = [
                                    'single' => 'Pago Individual',
                                    'package' => 'Paquete de Sesiones',
                                    'package_purchase' => 'Paquete de Sesiones',
                                    'refund' => 'Reembolso'
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $typeColors[$payment->payment_type] }}">
                                {{ $typeLabels[$payment->payment_type] }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Método(s) de Pago</label>
                            @php
                                $methodLabels = [
                                    'cash' => 'Efectivo',
                                    'transfer' => 'Transferencia',
                                    'credit_card' => 'Tarjeta Crédito',
                                    'debit_card' => 'Tarjeta Débito',
                                    'qr' => 'Pago QR'
                                ];
                            @endphp
                            <div class="text-lg text-gray-900 dark:text-white">
                                @foreach($payment->paymentDetails as $detail)
                                    <div>{{ $methodLabels[$detail->payment_method] ?? $detail->payment_method }} - ${{ number_format($detail->amount, 2) }}</div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Monto</label>
                            <div class="text-2xl font-bold {{ $payment->payment_type === 'refund' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $payment->payment_type === 'refund' ? '-' : '' }}${{ number_format($payment->total_amount, 2) }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Estado de Liquidación</label>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'liquidated' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    'not_applicable' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente de Liquidación',
                                    'liquidated' => 'Liquidado',
                                    'cancelled' => 'Cancelado',
                                    'not_applicable' => 'No Aplica'
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$payment->liquidation_status] }}">
                                {{ $statusLabels[$payment->liquidation_status] }}
                            </span>
                        </div>
                    </div>
                    
                    @if($payment->payment_type === 'package_purchase' && $payment->patientPackage)
                        <div class="mt-6 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                            <h4 class="font-medium text-purple-900 dark:text-purple-200 mb-2">Información del Paquete</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-purple-700 dark:text-purple-300">Sesiones incluidas:</span>
                                    <span class="font-medium text-purple-900 dark:text-purple-100 ml-1">{{ $payment->patientPackage->sessions_included }}</span>
                                </div>
                                <div>
                                    <span class="text-purple-700 dark:text-purple-300">Sesiones utilizadas:</span>
                                    <span class="font-medium text-purple-900 dark:text-purple-100 ml-1">{{ $payment->patientPackage->sessions_used }}</span>
                                </div>
                                <div>
                                    <span class="text-purple-700 dark:text-purple-300">Sesiones restantes:</span>
                                    <span class="font-medium text-purple-900 dark:text-purple-100 ml-1">{{ $payment->patientPackage->sessions_remaining }}</span>
                                </div>
                                <div>
                                    <span class="text-purple-700 dark:text-purple-300">Valor por sesión:</span>
                                    <span class="font-medium text-purple-900 dark:text-purple-100 ml-1">${{ number_format($payment->patientPackage->price_paid / $payment->patientPackage->sessions_included, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($payment->concept)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Concepto</label>
                            <div class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                {{ $payment->concept }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Turnos Asociados -->
            @if($payment->paymentAppointments->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                            </svg>
                            Turnos Asociados ({{ $payment->paymentAppointments->count() }})
                        </h2>

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
                                            @php
                                                $appointmentStatusColors = [
                                                    'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                    'attended' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                    'absent' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                                ];
                                                $appointmentStatusLabels = [
                                                    'scheduled' => 'Programado',
                                                    'attended' => 'Atendido',
                                                    'absent' => 'Ausente',
                                                    'cancelled' => 'Cancelado'
                                                ];
                                            @endphp
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full mt-1 {{ $appointmentStatusColors[$paymentAppointment->appointment->status] }}">
                                                {{ $appointmentStatusLabels[$paymentAppointment->appointment->status] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Información del Paciente -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Paciente
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nombre Completo</label>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $payment->patient->full_name }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">DNI</label>
                            <div class="font-mono text-gray-900 dark:text-white">{{ $payment->patient->dni }}</div>
                        </div>
                        
                        @if($payment->patient->email)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                <div class="text-gray-900 dark:text-white">{{ $payment->patient->email }}</div>
                            </div>
                        @endif
                        
                        @if($payment->patient->phone)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</label>
                                <div class="text-gray-900 dark:text-white">{{ $payment->patient->phone }}</div>
                            </div>
                        @endif
                    </div>
                    
                    {{-- <div class="mt-4">
                        <a href="{{ route('patients.show', $payment->patient) }}" 
                           class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium">
                            Ver perfil completo →
                        </a>
                    </div> --}}
                </div>
            </div>

            <!-- Acciones Rápidas -->
            @if($payment->payment_type === 'package_purchase' && $payment->patientPackage && $payment->patientPackage->sessions_remaining > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Usar Sesión del Paquete
                        </h3>

                        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4 mb-4">
                            <div class="text-sm text-purple-700 dark:text-purple-300">
                                <div class="font-medium">Sesiones disponibles: {{ $payment->patientPackage->sessions_remaining }}</div>
                                <div>Valor por sesión: ${{ number_format($payment->patientPackage->price_paid / $payment->patientPackage->sessions_included, 2) }}</div>
                            </div>
                        </div>

                        <button @click="showSessionModal = true"
                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Usar Sesión
                        </button>
                    </div>
                </div>
            @endif

            <!-- Resumen -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumen</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Fecha de creación:</span>
                            <span class="text-gray-900 dark:text-white">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        @if($payment->updated_at != $payment->created_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Última modificación:</span>
                                <span class="text-gray-900 dark:text-white">{{ $payment->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Turnos asociados:</span>
                            <span class="text-gray-900 dark:text-white">{{ $payment->paymentAppointments->count() }}</span>
                        </div>

                        @if($payment->paymentAppointments->count() > 0)
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block mb-1">Números de turno:</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($payment->paymentAppointments as $paymentAppointment)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 rounded">
                                            #{{ $paymentAppointment->appointment->id }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($payment->payment_type === 'package_purchase' && $payment->patientPackage)
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Progreso del paquete:</span>
                                <span class="text-gray-900 dark:text-white">{{ $payment->patientPackage->sessions_used }}/{{ $payment->patientPackage->sessions_included }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function paymentDetail() {
    return {
        showSessionModal: false,
        selectedAppointmentId: '',
        
        async useSession() {
            if (!this.selectedAppointmentId) {
                alert('Debe seleccionar un turno');
                return;
            }
            
            try {
                const response = await fetch(`/payments/{{ $payment->id }}/use-session`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        appointment_id: this.selectedAppointmentId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error al usar la sesión');
            }
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection