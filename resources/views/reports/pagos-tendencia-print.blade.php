@extends('layouts.print')

@section('title', 'Métodos de Pago – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.pagos.tendencia', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Métodos de Pago – Tendencia"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen por Método</h4>
            <div class="grid grid-cols-5 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Efectivo</span>
                        <span class="text-sm font-bold text-emerald-700">${{ number_format($totals['cash'], 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-blue-700">Transferencia</span>
                        <span class="text-sm font-bold text-blue-700">${{ number_format($totals['transfer'], 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-amber-700">Tarjeta</span>
                        <span class="text-sm font-bold text-amber-700">${{ number_format($totals['card'], 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-violet-700">QR</span>
                        <span class="text-sm font-bold text-violet-700">${{ number_format($totals['qr'], 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total</span>
                        <span class="text-sm font-bold text-gray-900">${{ number_format($totals['grand_total'], 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Detalle Mensual</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Mes</th>
                        <th class="report-th text-right">Efectivo</th>
                        <th class="report-th text-right">Transferencia</th>
                        <th class="report-th text-right">Tarjeta</th>
                        <th class="report-th text-right">QR</th>
                        <th class="report-th text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyTable as $row)
                    <tr>
                        <td class="report-td font-medium capitalize">{{ $row['label'] }}</td>
                        <td class="report-td text-right text-emerald-700">${{ number_format($row['cash'], 0, ',', '.') }}</td>
                        <td class="report-td text-right text-blue-700">${{ number_format($row['transfer'], 0, ',', '.') }}</td>
                        <td class="report-td text-right text-amber-700">${{ number_format($row['card'], 0, ',', '.') }}</td>
                        <td class="report-td text-right text-violet-700">${{ number_format($row['qr'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold">${{ number_format($row['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold">TOTAL</td>
                        <td class="report-td text-right font-bold text-emerald-700">${{ number_format($totals['cash'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold text-blue-700">${{ number_format($totals['transfer'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold text-amber-700">${{ number_format($totals['card'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold text-violet-700">${{ number_format($totals['qr'], 0, ',', '.') }}</td>
                        <td class="report-td text-right font-bold">${{ number_format($totals['grand_total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
