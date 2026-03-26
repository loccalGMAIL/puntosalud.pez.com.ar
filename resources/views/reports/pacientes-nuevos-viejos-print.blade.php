@extends('layouts.print')

@section('title', 'Nuevos vs Viejos – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.pacientes.nuevos-viejos', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Pacientes – Nuevos vs Recurrentes"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen Global</h4>
            <div class="grid grid-cols-3 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Nuevos</span>
                        <span class="text-sm font-bold text-emerald-700">{{ number_format($totals['new']) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-blue-700">Volvieron</span>
                        <span class="text-sm font-bold text-blue-700">{{ number_format($totals['returning']) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($totals['total']) }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Detalle Mensual</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Mes</th>
                        <th class="report-th text-right">Nuevos</th>
                        <th class="report-th text-right">Volvieron</th>
                        <th class="report-th text-right">Total</th>
                        <th class="report-th text-right">% Nuevos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    <tr>
                        <td class="report-td font-medium capitalize">{{ $m['label'] }}</td>
                        <td class="report-td text-right text-emerald-700 font-medium">{{ $m['new'] }}</td>
                        <td class="report-td text-right text-blue-700 font-medium">{{ $m['returning'] }}</td>
                        <td class="report-td text-right font-bold">{{ $m['total'] }}</td>
                        <td class="report-td text-right text-gray-600">
                            {{ $m['total'] > 0 ? round(($m['new'] / $m['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold">TOTAL</td>
                        <td class="report-td text-right font-bold text-emerald-700">{{ $totals['new'] }}</td>
                        <td class="report-td text-right font-bold text-blue-700">{{ $totals['returning'] }}</td>
                        <td class="report-td text-right font-bold">{{ $totals['total'] }}</td>
                        <td class="report-td text-right font-bold text-gray-600">
                            {{ $totals['total'] > 0 ? round(($totals['new'] / $totals['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
