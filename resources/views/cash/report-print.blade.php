@extends('layouts.print')

@php
    $groupLabels = ['day' => 'Diario', 'week' => 'Semanal', 'month' => 'Mensual'];
    $groupLabel = $groupLabels[$groupBy] ?? $groupBy;
@endphp

@section('title', 'Análisis de Caja - ' . $dateFrom . ' al ' . $dateTo)
@section('back-url', route('cash.report'))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">

        <!-- Encabezado del Reporte -->
        <div class="p-2 border-b border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Análisis de Caja"
                subtitle="Período: {{ $dateFrom }} al {{ $dateTo }} · Agrupado {{ $groupLabel }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <!-- Resumen Financiero -->
            <h4 class="report-section-title">Resumen Financiero</h4>
            <div class="grid grid-cols-4 gap-4 mb-6 print:gap-1 print:mb-2">
                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-green-900 dark:text-green-200 print:text-gray-800">Ingresos</span>
                        <span class="text-base font-bold text-green-600 dark:text-green-400 print:text-black print:text-sm">+${{ number_format($summary['total_inflows'], 2) }}</span>
                    </p>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-red-900 dark:text-red-200 print:text-gray-800">Egresos</span>
                        <span class="text-base font-bold text-red-600 dark:text-red-400 print:text-black print:text-sm">-${{ number_format($summary['total_outflows'], 2) }}</span>
                    </p>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-purple-900 dark:text-purple-200 print:text-gray-800">Resultado Neto</span>
                        <span class="text-base font-bold print:text-sm {{ $summary['net_amount'] >= 0 ? 'text-purple-600 dark:text-purple-400 print:text-black' : 'text-red-600 dark:text-red-400 print:text-black' }}">
                            {{ $summary['net_amount'] >= 0 ? '+' : '' }}${{ number_format($summary['net_amount'], 2) }}
                        </span>
                    </p>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-blue-900 dark:text-blue-200 print:text-gray-800">Movimientos</span>
                        <span class="text-base font-bold text-blue-600 dark:text-blue-400 print:text-black print:text-sm">{{ number_format($summary['movements_count']) }}</span>
                    </p>
                    <p class="text-[9px] text-gray-500 dark:text-gray-400 print:text-gray-600 text-right">en {{ $summary['period_days'] }} días</p>
                </div>
            </div>

            <!-- Análisis por Tipo de Movimiento -->
            @if($movementsByType->count() > 0)
            <div class="mb-2 print:mb-0.5">
                <h5 class="report-section-title">Análisis por Tipo de Movimiento</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] print:text-[9px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">Tipo</th>
                                <th class="text-center py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">Cant.</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">Ingresos</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">Egresos</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">Neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                            @foreach($movementsByType as $type => $data)
                            <tr>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                    {{ $data['icon'] ?? '' }} {{ $data['type_name'] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                </td>
                                <td class="py-[1px] px-1 text-center text-gray-600 dark:text-gray-400 print:text-gray-700">{{ $data['count'] }}</td>
                                <td class="py-[1px] px-1 text-right text-green-600 dark:text-green-400 print:text-green-700">
                                    +${{ number_format($data['inflows'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">
                                    -${{ number_format($data['outflows'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right font-medium text-gray-900 dark:text-white print:text-black">
                                    ${{ number_format($data['inflows'] - $data['outflows'], 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Detalle por Período -->
            @if($reportData->count() > 0)
            <div class="mb-2 print:mb-0.5">
                <h5 class="report-section-title">Detalle por Período ({{ $reportData->count() }} períodos)</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] print:text-[9px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:30%">Período</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:18%">Ingresos</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:18%">Egresos</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:18%">Neto</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:16%">Movimientos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                            @foreach($reportData as $period)
                            <tr>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">{{ $period['period_label'] }}</td>
                                <td class="py-[1px] px-1 text-right text-green-600 dark:text-green-400 print:text-green-700">
                                    +${{ number_format($period['inflows'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">
                                    -${{ number_format($period['outflows'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right font-medium {{ $period['net'] >= 0 ? 'text-gray-900 dark:text-white print:text-black' : 'text-red-600 dark:text-red-400 print:text-red-700' }}">
                                    {{ $period['net'] >= 0 ? '+' : '' }}${{ number_format($period['net'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right text-gray-600 dark:text-gray-400 print:text-gray-700">{{ $period['count'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-500 print:border-gray-500">
                                <td class="py-[1px] px-1 font-bold text-gray-900 dark:text-white print:text-black">TOTALES</td>
                                <td class="py-[1px] px-1 text-right font-bold text-green-600 dark:text-green-400 print:text-green-700">
                                    +${{ number_format($summary['total_inflows'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right font-bold text-red-600 dark:text-red-400 print:text-red-700">
                                    -${{ number_format($summary['total_outflows'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right font-bold {{ $summary['net_amount'] >= 0 ? 'text-gray-900 dark:text-white print:text-black' : 'text-red-600 dark:text-red-400 print:text-red-700' }}">
                                    {{ $summary['net_amount'] >= 0 ? '+' : '' }}${{ number_format($summary['net_amount'], 2) }}
                                </td>
                                <td class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                    {{ $summary['movements_count'] }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

        </div>

        <!-- Pie del Reporte -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ auth()->user()->name }}</p>
                <p>Total de movimientos: {{ $summary['movements_count'] }}</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
            window.addEventListener('afterprint', function() { window.close(); });
            setTimeout(function() { window.close(); }, 3000);
        }, 500);
    }
</script>
@endpush
