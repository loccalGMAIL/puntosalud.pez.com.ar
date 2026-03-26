@extends('layouts.print')

@section('title', 'Flujo Mensual de Caja – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.flujo-caja-mensual', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Flujo Mensual de Caja"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen Global</h4>
            <div class="grid grid-cols-3 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Total Ingresos</span>
                        <span class="text-sm font-bold text-emerald-700">${{ number_format($totals['income'], 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-red-700">Total Egresos</span>
                        <span class="text-sm font-bold text-red-700">${{ number_format($totals['expenses'], 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium {{ $totals['balance'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">Saldo Neto</span>
                        <span class="text-sm font-bold {{ $totals['balance'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">${{ number_format($totals['balance'], 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Detalle Mensual</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Mes</th>
                        <th class="report-th text-right">Ingresos</th>
                        <th class="report-th text-right">Egresos</th>
                        <th class="report-th text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthly as $m)
                    <tr>
                        <td class="report-td font-medium capitalize">{{ $m['label'] }}</td>
                        <td class="report-td text-right text-emerald-700 font-medium">${{ number_format($m['income'], 0, ',', '.') }}</td>
                        <td class="report-td text-right text-red-700 font-medium">${{ number_format($m['expenses'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold {{ $m['balance'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                            ${{ number_format($m['balance'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold">TOTAL</td>
                        <td class="report-td text-right font-bold text-emerald-700">${{ number_format($totals['income'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold text-red-700">${{ number_format($totals['expenses'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold {{ $totals['balance'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">${{ number_format($totals['balance'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
