@extends('layouts.print')

@section('title', 'Ausentismo de Pacientes – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.pacientes.ausentismo', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Pacientes – Ausentismo"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen Global</h4>
            <div class="grid grid-cols-3 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total Completados</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($globalTotal) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-yellow-700">Total Ausentes</span>
                        <span class="text-sm font-bold text-yellow-700">{{ number_format($globalAbsent) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-red-700">Tasa Ausentismo</span>
                        <span class="text-sm font-bold text-red-700">{{ $globalRate }}%</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Detalle por Profesional</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Profesional</th>
                        <th class="report-th text-left">Especialidad</th>
                        <th class="report-th text-center">Atendidos</th>
                        <th class="report-th text-center">Ausentes</th>
                        <th class="report-th text-center">Total</th>
                        <th class="report-th text-center">Tasa Ausentismo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats as $s)
                    <tr>
                        <td class="report-td font-medium">{{ $s['full_name'] }}</td>
                        <td class="report-td text-gray-600">{{ $s['specialty'] }}</td>
                        <td class="report-td text-center text-emerald-700">{{ $s['attended'] }}</td>
                        <td class="report-td text-center text-yellow-700">{{ $s['absent'] }}</td>
                        <td class="report-td text-center font-bold">{{ $s['total'] }}</td>
                        <td class="report-td text-center font-bold {{ $s['absence_rate'] >= 30 ? 'text-red-700' : ($s['absence_rate'] >= 15 ? 'text-yellow-700' : 'text-emerald-700') }}">
                            {{ $s['absence_rate'] }}%
                        </td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold" colspan="2">TOTAL</td>
                        <td class="report-td text-center font-bold text-emerald-700">{{ $globalTotal - $globalAbsent }}</td>
                        <td class="report-td text-center font-bold text-yellow-700">{{ $globalAbsent }}</td>
                        <td class="report-td text-center font-bold">{{ $globalTotal }}</td>
                        <td class="report-td text-center font-bold text-red-700">{{ $globalRate }}%</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    if (window.location.search.includes('print=1')) {
        window.onload = function() {
            setTimeout(function() {
                window.print();
                window.addEventListener('afterprint', function() { window.close(); });
                setTimeout(function() { window.close(); }, 3000);
            }, 500);
        }
    }
</script>
@endpush
