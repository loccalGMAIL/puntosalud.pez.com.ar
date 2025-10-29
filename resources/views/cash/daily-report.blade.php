@extends('layouts.print')

@section('title', 'Reporte de Cierre del Día - ' . $selectedDate->format('d/m/Y'))
@section('mobileTitle', 'Reporte Diario')

@section('content')
<div class="p-6 print:p-1" x-data="dailyReportForm()">

    <!-- Contenido del Reporte -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">
        <!-- Encabezado del Reporte -->
        <div class="p-2 border-b border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-0.5">
            <div class="flex items-center justify-between gap-2">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="Logo PuntoSalud" class="w-32 h-32 print:w-24 print:h-24 object-contain">
                </div>

                <!-- Información del Reporte -->
                <div class="flex-1 text-center space-y-0.5">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 print:text-gray-700 print:text-sm">Reporte de Cierre de Caja</h3>
                    <p class="text-gray-600 dark:text-gray-400 print:text-gray-600 print:text-xs">{{ $selectedDate->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 print:text-gray-500 print:text-xs">Generado: {{ now()->format('d/m/Y H:i') }}</p>
                </div>

                <!-- Espacio para balance visual -->
                <div class="flex-shrink-0 w-32 print:w-24"></div>
            </div>
        </div>

        <!-- Resumen Financiero -->
        <div class="p-6 print:p-2">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white print:text-black mb-4 print:mb-2 print:text-sm">Resumen Financiero</h4>

            <div class="grid grid-cols-4 gap-4 mb-6 print:gap-1 print:mb-2">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="text-xs font-medium text-blue-900 dark:text-blue-200 print:text-gray-800 print:text-xs">Saldo Inicial</p>
                    <p class="text-base font-bold text-blue-600 dark:text-blue-400 print:text-black print:text-sm">
                        ${{ number_format($summary['initial_balance'], 2) }}
                    </p>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="text-xs font-medium text-green-900 dark:text-green-200 print:text-gray-800 print:text-xs">Total Ingresos</p>
                    <p class="text-base font-bold text-green-600 dark:text-green-400 print:text-black print:text-sm">
                        +${{ number_format($summary['total_inflows'], 2) }}
                    </p>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="text-xs font-medium text-red-900 dark:text-red-200 print:text-gray-800 print:text-xs">Total Egresos</p>
                    <p class="text-base font-bold text-red-600 dark:text-red-400 print:text-black print:text-sm">
                        -${{ number_format($summary['total_outflows'], 2) }}
                    </p>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="text-xs font-medium text-purple-900 dark:text-purple-200 print:text-gray-800 print:text-xs">Saldo Final Teórico</p>
                    <p class="text-base font-bold text-purple-600 dark:text-purple-400 print:text-black print:text-sm">
                        ${{ number_format($summary['final_balance'], 2) }}
                    </p>
                </div>
            </div>

            @if($summary['closing_movement'])
            <!-- Estado de Cierre -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6 print:bg-gray-100 print:border print:border-gray-300 print:p-1 print:mb-2">
                <h5 class="font-semibold text-gray-900 dark:text-white print:text-black print:text-xs">Estado de Cierre:</h5>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm print:text-xs">
                    <div class="flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 print:text-gray-600">Contado:</span>
                        <span class="font-bold text-gray-900 dark:text-white print:text-black ml-1">
                            ${{ number_format($summary['counted_amount'], 2) }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 print:text-gray-600">Diferencia:</span>
                        <span class="font-bold @if($summary['difference'] > 0) text-green-600 @elseif($summary['difference'] < 0) text-red-600 @else text-gray-900 dark:text-white @endif print:text-black ml-1">
                            ${{ number_format($summary['difference'], 2) }}
                            @if($summary['difference'] > 0) (Sobra) @elseif($summary['difference'] < 0) (Falta) @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 print:text-gray-600">Cerrado por:</span>
                        <span class="font-bold text-gray-900 dark:text-white print:text-black ml-1">
                            {{ $summary['closing_movement']?->user?->name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Desglose por Tipo de Movimiento -->
            @if($movementsByType->count() > 0)
            <div class="mb-6 print:mb-2">
                <h5 class="font-semibold text-gray-900 dark:text-white print:text-black mb-3 print:mb-1 print:text-xs">Desglose por Tipo de Movimiento</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm print:text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Tipo</th>
                                <th class="text-center py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Cant.</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Ingresos</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Egresos</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600 print:divide-gray-400">
                            @foreach($movementsByType as $type => $data)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-white print:text-black print:py-0.5">
                                    @switch($type)
                                        @case('patient_payment') 💰 Pagos Pacientes @break
                                        @case('professional_payment') 👨‍⚕️ Pagos Profesionales @break
                                        @case('expense') 💸 Gastos @break
                                        @case('refund') 🔄 Reembolsos @break
                                        @case('cash_opening') 🔓 Apertura de Caja @break
                                        @case('cash_closing') 🔒 Cierre de Caja @break
                                        @case('cash_withdrawal') 💸 Retiro de Caja @break
                                        @case('other') 📝 Otros Ingresos @break
                                        @default {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    @endswitch
                                </td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-400 print:text-gray-600 print:py-0.5">{{ $data['count'] }}</td>
                                <td class="py-2 text-right text-green-600 dark:text-green-400 print:text-green-700 print:py-0.5">+${{ number_format($data['inflows'], 2) }}</td>
                                <td class="py-2 text-right text-red-600 dark:text-red-400 print:text-red-700 print:py-0.5">-${{ number_format($data['outflows'], 2) }}</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-white print:text-black print:py-0.5">
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

            <!-- Liquidación por Profesional -->
            @if($professionalIncome->count() > 0)
            <div class="mb-6 print:mb-2">
                <h5 class="font-semibold text-gray-900 dark:text-white print:text-black mb-3 print:mb-1 print:text-xs">Liquidación por Profesional</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm print:text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Profesional</th>
                                <th class="text-left py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Especialidad</th>
                                <th class="text-center py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Cons.</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Total Cobrado</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Profesional</th>
                                <th class="text-right py-2 font-semibold text-gray-900 dark:text-white print:text-black print:py-0.5">Clínica</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600 print:divide-gray-400">
                            @foreach($professionalIncome as $data)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-white print:text-black print:py-0.5">{{ $data['full_name'] }}</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400 print:text-gray-600 print:py-0.5">{{ $data['specialty'] }}</td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-400 print:text-gray-600 print:py-0.5">{{ $data['count'] }}</td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-white print:text-black print:py-0.5">
                                    ${{ number_format($data['total_collected'], 2) }}
                                </td>
                                <td class="py-2 text-right font-medium text-blue-600 dark:text-blue-400 print:text-blue-700 print:py-0.5">
                                    ${{ number_format($data['professional_amount'], 2) }}
                                </td>
                                <td class="py-2 text-right font-medium text-green-600 dark:text-green-400 print:text-green-700 print:py-0.5">
                                    ${{ number_format($data['clinic_amount'], 2) }}
                                </td>
                            </tr>
                            @endforeach
                            <tr class="border-t-2 border-gray-300 dark:border-gray-500 print:border-gray-500">
                                <td colspan="3" class="py-2 text-right font-bold text-gray-900 dark:text-white print:text-black print:py-0.5">TOTALES:</td>
                                <td class="py-2 text-right font-bold text-gray-900 dark:text-white print:text-black print:py-0.5">
                                    ${{ number_format($professionalIncome->sum('total_collected'), 2) }}
                                </td>
                                <td class="py-2 text-right font-bold text-blue-600 dark:text-blue-400 print:text-blue-700 print:py-0.5">
                                    ${{ number_format($professionalIncome->sum('professional_amount'), 2) }}
                                </td>
                                <td class="py-2 text-right font-bold text-green-600 dark:text-green-400 print:text-green-700 print:py-0.5">
                                    ${{ number_format($professionalIncome->sum('clinic_amount'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Pie del Reporte -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-1">
            <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 print:text-gray-600 print:text-xs">
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
                    // Cerrar la ventana después de imprimir
                    setTimeout(() => window.close(), 500);
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

@media print {
    @page {
        margin: 0.5cm;
        size: A4;
    }
    body {
        margin: 0;
        padding: 0;
    }
    /* Ocultar encabezados y pies de página del navegador */
    @page :first {
        margin-top: 0;
    }
    @page {
        margin-top: 0;
    }
}
</style>
@endpush

@endsection