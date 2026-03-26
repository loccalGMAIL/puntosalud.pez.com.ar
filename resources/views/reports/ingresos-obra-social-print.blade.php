@extends('layouts.print')

@section('title', 'Ingresos por Obra Social – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.ingresos-obra-social', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Ingresos por Obra Social"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen</h4>
            <div class="grid grid-cols-2 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total Turnos</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($totals['count']) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Total Ingresos</span>
                        <span class="text-sm font-bold text-emerald-700">${{ number_format($totals['amount'], 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Detalle por Obra Social</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Obra Social / Financiador</th>
                        <th class="report-th text-right">Turnos</th>
                        <th class="report-th text-right">% Turnos</th>
                        <th class="report-th text-right">Total Ingresos</th>
                        <th class="report-th text-right">% Ingresos</th>
                        <th class="report-th text-right">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byInsurance as $ins)
                    <tr>
                        <td class="report-td font-medium capitalize">{{ $ins['name'] }}</td>
                        <td class="report-td text-right">{{ $ins['count'] }}</td>
                        <td class="report-td text-right text-gray-600">
                            {{ $totals['count'] > 0 ? round(($ins['count'] / $totals['count']) * 100, 1) : 0 }}%
                        </td>
                        <td class="report-td text-right font-bold text-emerald-700">${{ number_format($ins['amount'], 0, ',', '.') }}</td>
                        <td class="report-td text-right text-gray-600">
                            {{ $totals['amount'] > 0 ? round(($ins['amount'] / $totals['amount']) * 100, 1) : 0 }}%
                        </td>
                        <td class="report-td text-right text-gray-600">${{ number_format($ins['avg'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold">TOTAL</td>
                        <td class="report-td text-right font-bold">{{ $totals['count'] }}</td>
                        <td class="report-td text-right font-bold">100%</td>
                        <td class="report-td text-right font-bold text-emerald-700">${{ number_format($totals['amount'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold">100%</td>
                        <td class="report-td text-right">—</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
