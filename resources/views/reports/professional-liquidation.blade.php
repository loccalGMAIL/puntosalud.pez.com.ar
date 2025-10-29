@extends('layouts.app')

@section('title', 'Liquidación Profesional - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                💰 Liquidación del Profesional
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Dr. {{ $liquidationData['professional']->full_name }} - {{ $liquidationData['date']->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('reports.professional-liquidation') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Volver
            </a>
            <button onclick="window.print()"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Imprimir
            </button>
            @if($liquidationData['totals']['professional_amount'] > 0)
            <button onclick="liquidarProfesional({{ $liquidationData['professional']->id }}, '{{ $liquidationData['professional']->full_name }}', {{ $liquidationData['totals']['professional_amount'] }})"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m-1.125 3.75c0-.621.504-1.125 1.125-1.125h1.5v1.5h-1.5A1.125 1.125 0 013.375 8.25zM6 21V3.75h.75A1.875 1.875 0 018.625 2.25H12m0 0h3.375c1.035 0 1.875.84 1.875 1.875v16.5h-6" />
                </svg>
                Liquidar
            </button>
            @endif
        </div>
    </div>

    <!-- Professional Info -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Profesional</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Dr. {{ $liquidationData['professional']->full_name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $liquidationData['professional']->specialty->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $liquidationData['date']->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $liquidationData['date']->translatedFormat('l') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Comisión</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $liquidationData['totals']['commission_percentage'] }}%</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $liquidationData['appointments']->count() }} pacientes atendidos</p>
            </div>
        </div>
    </div>

    <!-- Liquidation Summary -->
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-4">
            💰 Resumen de Liquidación
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-emerald-700 dark:text-emerald-300">Total Facturado:</span>
                <span class="font-medium text-emerald-900 dark:text-emerald-100">${{ number_format($liquidationData['totals']['total_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-emerald-700 dark:text-emerald-300">Comisión Clínica ({{ 100 - $liquidationData['totals']['commission_percentage'] }}%):</span>
                <span class="font-medium text-emerald-900 dark:text-emerald-100">-${{ number_format($liquidationData['totals']['clinic_amount'], 0, ',', '.') }}</span>
            </div>
            @if($liquidationData['totals']['total_refunds'] > 0)
            <div class="flex justify-between text-sm">
                <span class="text-red-700 dark:text-red-300">Reintegros a Pacientes:</span>
                <span class="font-medium text-red-900 dark:text-red-100">-${{ number_format($liquidationData['totals']['total_refunds'], 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="border-t border-emerald-200 dark:border-emerald-800 pt-3">
                <div class="flex justify-between">
                    <span class="font-semibold text-emerald-900 dark:text-emerald-100">MONTO A ENTREGAR AL PROFESIONAL:</span>
                    <span class="text-xl font-bold text-emerald-700 dark:text-emerald-400">${{ number_format($liquidationData['totals']['professional_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Turnos Pagados Previamente -->
    @if($liquidationData['prepaid_appointments']->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    💳 Turnos Pagados Previamente ({{ $liquidationData['prepaid_appointments']->count() }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total: ${{ number_format($liquidationData['totals']['prepaid_amount'], 0, ',', '.') }} | 
                    Su comisión: ${{ number_format($liquidationData['totals']['prepaid_professional'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['prepaid_appointments'] as $appointment)
                                <tr class="border-b border-gray-100 dark:border-gray-700 @if($appointment['is_urgency']) bg-red-50/30 dark:bg-red-900/10 @endif">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            @if($appointment['is_urgency'])
                                                <span class="inline-flex items-center rounded px-1 py-0.5 text-xs font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700" title="Urgencia">
                                                    🚨
                                                </span>
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $appointment['time'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $appointment['patient_dni'] }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ match($appointment['payment_method']) {
                                                'cash' => 'Efectivo',
                                                'transfer' => 'Transferencia', 
                                                'card' => 'Tarjeta',
                                                default => $appointment['payment_method']
                                            } }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $appointment['payment_date'] }}</div>
                                        @if($appointment['receipt_number'])
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Rec: {{ $appointment['receipt_number'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Turnos Pagados Hoy -->
    @if($liquidationData['today_paid_appointments']->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    💰 Turnos Cobrados Hoy ({{ $liquidationData['today_paid_appointments']->count() }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total: ${{ number_format($liquidationData['totals']['today_paid_amount'], 0, ',', '.') }} | 
                    Su comisión: ${{ number_format($liquidationData['totals']['today_paid_professional'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Método</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['today_paid_appointments'] as $appointment)
                                <tr class="border-b border-gray-100 dark:border-gray-700 @if($appointment['is_urgency']) bg-red-50/30 dark:bg-red-900/10 @endif">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            @if($appointment['is_urgency'])
                                                <span class="inline-flex items-center rounded px-1 py-0.5 text-xs font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700" title="Urgencia">
                                                    🚨
                                                </span>
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $appointment['time'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $appointment['patient_dni'] }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ match($appointment['payment_method']) {
                                                'cash' => 'Efectivo',
                                                'transfer' => 'Transferencia', 
                                                'card' => 'Tarjeta',
                                                default => $appointment['payment_method']
                                            } }}
                                        </div>
                                        @if($appointment['receipt_number'])
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Rec: {{ $appointment['receipt_number'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Turnos Sin Pagar -->
    @if($liquidationData['unpaid_appointments']->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-red-50 dark:bg-red-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    ⚠️ Turnos Pendientes de Pago ({{ $liquidationData['unpaid_appointments']->count() }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total pendiente: ${{ number_format($liquidationData['totals']['unpaid_amount'], 0, ',', '.') }} | 
                    Su comisión pendiente: ${{ number_format($liquidationData['totals']['unpaid_professional'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['unpaid_appointments'] as $appointment)
                                <tr class="border-b border-gray-100 dark:border-gray-700 @if($appointment['is_urgency']) bg-red-50/30 dark:bg-red-900/10 @endif">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            @if($appointment['is_urgency'])
                                                <span class="inline-flex items-center rounded px-1 py-0.5 text-xs font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700" title="Urgencia">
                                                    🚨
                                                </span>
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $appointment['time'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $appointment['patient_dni'] }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                            PENDIENTE
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Reintegros a Pacientes -->
    @if(count($liquidationData['refunds']) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    🔄 Reintegros a Pacientes ({{ count($liquidationData['refunds']) }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Montos devueltos que se descuentan de su liquidación: ${{ number_format($liquidationData['totals']['total_refunds'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['refunds'] as $refund)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 px-3 font-medium text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($refund->created_at)->format('H:i') }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $refund->description }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-red-600 dark:text-red-400">
                                        -${{ number_format(abs($refund->amount), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                <td colspan="2" class="py-3 px-3 text-right font-semibold text-gray-900 dark:text-white">
                                    Total Reintegros:
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-red-600 dark:text-red-400">
                                    -${{ number_format($liquidationData['totals']['total_refunds'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
        Liquidación generada el {{ $liquidationData['generated_at']->format('d/m/Y H:i:s') }} por {{ $liquidationData['generated_by'] }}
    </div>
</div>

<script>
// Función para liquidar profesional
async function liquidarProfesional(professionalId, professionalName, amount) {
    // Mostrar modal de confirmación
    const confirmed = await SystemModal.confirm(
        'Confirmar Liquidación',
        `¿Confirmar liquidación de <strong>${professionalName}</strong> por <strong>$${amount.toLocaleString()}</strong>?<br><br>Esto registrará el pago en caja y descontará el monto del efectivo disponible.`,
        'Liquidar',
        'Cancelar'
    );

    if (!confirmed) return;

    try {
        const response = await fetch('/liquidation/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                professional_id: professionalId,
                amount: amount,
                date: '{{ $liquidationData['date']->format('Y-m-d') }}'
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Error en la operación');
        }

        // Mostrar mensaje de éxito
        await SystemModal.show(
            'success',
            'Liquidación Procesada',
            `${professionalName}\nMonto: $${amount.toLocaleString()}\nNuevo saldo en caja: $${result.data.new_balance.toLocaleString()}`,
            'Aceptar'
        );

        // Recargar la página para actualizar los datos
        window.location.reload();

    } catch (error) {
        // Mostrar modal de error
        await SystemModal.show(
            'error',
            'Error al Procesar Liquidación',
            error.message,
            'Aceptar'
        );
        console.error('Error:', error);
    }
}

// Auto-imprimir si viene desde el selector de reportes
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('autoPrint') === 'true') {
        sessionStorage.removeItem('autoPrint');
        // Pequeño delay para que la página cargue completamente
        setTimeout(function() {
            window.print();
        }, 500);
    }
});
</script>

<style>
@media print {
    /* Ocultar sidebar y elementos de navegación */
    [x-data]:first-of-type > div:first-child,  /* Sidebar container */
    .fixed.left-0.top-0,  /* Sidebar fixed */
    .fixed.inset-0.z-40,  /* Overlay mobile */
    nav,
    .no-print,
    button,
    .bg-gray-600,
    header,
    aside,
    .lg\:hidden {  /* Mobile header */
        display: none !important;
    }

    /* Resetear el margin-left del contenido principal */
    [class*="lg:ml-"] {
        margin-left: 0 !important;
    }

    /* Ajustar el container para impresión */
    .container {
        max-width: 100% !important;
        padding: 0 !important;
    }

    /* Resetear colores de fondo para impresión */
    body {
        background: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Asegurar que los badges y colores se vean bien */
    .bg-emerald-50,
    .bg-emerald-100,
    .bg-yellow-50,
    .bg-yellow-100,
    .bg-green-50,
    .bg-green-100,
    .bg-red-50,
    .bg-red-100,
    .bg-gray-100 {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Ocultar botones del header */
    .mb-6.flex.items-center.justify-between > div:last-child {
        display: none !important;
    }

    /* Ajustar tamaños de fuente para impresión */
    body {
        font-size: 12pt;
    }

    h1 {
        font-size: 18pt;
    }

    table {
        page-break-inside: avoid;
    }

    tr {
        page-break-inside: avoid;
    }
}
</style>

@endsection