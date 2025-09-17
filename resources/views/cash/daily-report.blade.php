@extends('layouts.print')

@section('title', 'Reporte de Cierre del Día - ' . $selectedDate->format('d/m/Y'))
@section('mobileTitle', 'Reporte Diario')

@section('content')
<div class="p-6 print:p-2" x-data="dailyReportForm()">

    <!-- Contenido del Reporte -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">
        <!-- Encabezado del Reporte -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 print:border-gray-400">
            <div class="text-center">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white print:text-black">PuntoSalud</h2>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 print:text-gray-700">Reporte de Cierre de Caja</h3>
                <p class="text-gray-600 dark:text-gray-400 print:text-gray-600">{{ $selectedDate->format('l, d \d\e F \d\e Y') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-500 print:text-gray-500">Generado: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Resumen Financiero -->
        <div class="p-6 print:p-4">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white print:text-black mb-4">Resumen Financiero</h4>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 print:gap-2">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-200 print:text-gray-800">Saldo Inicial</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400 print:text-black">
                        ${{ number_format($summary['initial_balance'], 2) }}
                    </p>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300">
                    <p class="text-sm font-medium text-green-900 dark:text-green-200 print:text-gray-800">Total Ingresos</p>
                    <p class="text-xl font-bold text-green-600 dark:text-green-400 print:text-black">
                        +${{ number_format($summary['total_inflows'], 2) }}
                    </p>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300">
                    <p class="text-sm font-medium text-red-900 dark:text-red-200 print:text-gray-800">Total Egresos</p>
                    <p class="text-xl font-bold text-red-600 dark:text-red-400 print:text-black">
                        -${{ number_format($summary['total_outflows'], 2) }}
                    </p>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300">
                    <p class="text-sm font-medium text-purple-900 dark:text-purple-200 print:text-gray-800">Saldo Final Teórico</p>
                    <p class="text-xl font-bold text-purple-600 dark:text-purple-400 print:text-black">
                        ${{ number_format($summary['final_balance'], 2) }}
                    </p>
                </div>
            </div>

            @if($summary['closing_movement'])
            <!-- Estado de Cierre -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6 print:bg-gray-100 print:border print:border-gray-300">
                <h5 class="font-semibold text-gray-900 dark:text-white print:text-black mb-2">Estado de Cierre</h5>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 print:text-gray-600">Efectivo Contado:</p>
                        <p class="font-bold text-gray-900 dark:text-white print:text-black">
                            ${{ number_format($summary['counted_amount'], 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 print:text-gray-600">Diferencia:</p>
                        <p class="font-bold @if($summary['difference'] > 0) text-green-600 @elseif($summary['difference'] < 0) text-red-600 @else text-gray-900 dark:text-white @endif print:text-black">
                            ${{ number_format($summary['difference'], 2) }}
                            @if($summary['difference'] > 0) (Sobrante) @elseif($summary['difference'] < 0) (Faltante) @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 print:text-gray-600">Cerrado por:</p>
                        <p class="font-bold text-gray-900 dark:text-white print:text-black">
                            {{ $summary['closing_movement']?->user?->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            @else
            <!-- Caja Sin Cerrar -->
            <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg mb-6 print:bg-yellow-100 print:border print:border-yellow-400">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400 print:text-amber-700 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="font-semibold text-amber-800 dark:text-amber-200 print:text-amber-800">
                        Caja sin cerrar - Se requiere conteo de efectivo y cierre
                    </p>
                </div>
            </div>
            @endif

            <!-- Desglose por Tipo de Movimiento -->
            @if($movementsByType->count() > 0)
            <div class="mb-6">
                <h5 class="font-semibold text-gray-900 dark:text-white print:text-black mb-3">Desglose por Tipo de Movimiento</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-2 font-semibold text-gray-900 dark:text-white print:text-black">Tipo</th>
                                <th class="text-center py-2 font-semibold text-gray-900 dark:text-white print:text-black">Cantidad</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black">Ingresos</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black">Egresos</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black">Neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600 print:divide-gray-400">
                            @foreach($movementsByType as $type => $data)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-white print:text-black">
                                    @switch($type)
                                        @case('patient_payment') 💰 Pagos Pacientes @break
                                        @case('professional_payment') 👨‍⚕️ Pagos Profesionales @break
                                        @case('expense') 💸 Gastos @break
                                        @case('refund') 🔄 Reembolsos @break
                                        @case('cash_opening') 🔓 Apertura de Caja @break
                                        @case('cash_closing') 🔒 Cierre de Caja @break
                                        @case('cash_withdrawal') 💸 Retiro de Caja @break
                                        @default {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    @endswitch
                                </td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-400 print:text-gray-600">{{ $data['count'] }}</td>
                                <td class="py-2 text-right text-green-600 dark:text-green-400 print:text-green-700">+${{ number_format($data['inflows'], 2) }}</td>
                                <td class="py-2 text-right text-red-600 dark:text-red-400 print:text-red-700">-${{ number_format($data['outflows'], 2) }}</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-white print:text-black">
                                    ${{ number_format($data['inflows'] - $data['outflows'], 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Resumen de Usuarios -->
            @if($userSummary->count() > 0)
            <div class="mb-6">
                <h5 class="font-semibold text-gray-900 dark:text-white print:text-black mb-3">Actividad por Usuario</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-2 font-semibold text-gray-900 dark:text-white print:text-black">Usuario</th>
                                <th class="text-center py-2 font-semibold text-gray-900 dark:text-white print:text-black">Movimientos</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600 print:divide-gray-400">
                            @foreach($userSummary as $user => $data)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-white print:text-black">{{ $user }}</td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-400 print:text-gray-600">{{ $data['count'] }}</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-white print:text-black">
                                    ${{ number_format($data['total'], 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Pie del Reporte -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-4">
            <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 print:text-gray-600">
                <p>Total de movimientos: {{ $movements->count() }}</p>
                <p>Generado por: {{ auth()->user()->name }}</p>
            </div>
        </div>
    </div>
</div>

<script>
function dailyReportForm() {
    return {
        init() {
            // Auto-print si viene con parámetro print
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === 'true') {
                setTimeout(() => {
                    window.print();
                    // Si es una ventana popup, cerrarla después de imprimir
                    if (window.opener) {
                        setTimeout(() => window.close(), 1000);
                    }
                }, 500);
            }
        }
    }
}
</script>

@push('styles')
<style>
/* Estilos específicos para el reporte de caja */
.page-break-inside-avoid {
    page-break-inside: avoid;
}
</style>
@endpush

@endsection