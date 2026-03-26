@extends('layouts.print')

@section('title', 'Frecuencia de Visitas – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.pacientes.frecuencia', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Pacientes – Frecuencia de Visitas"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen</h4>
            <div class="grid grid-cols-3 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total Pacientes</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($totalPatients) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-blue-700">Con &gt;1 Visita</span>
                        <span class="text-sm font-bold text-blue-700">{{ number_format($patientsWithMultiple) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Promedio días</span>
                        <span class="text-sm font-bold text-emerald-700">{{ $globalAvg ?? '—' }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Distribución por Frecuencia</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Rango de Días entre Visitas</th>
                        <th class="report-th text-right">Pacientes</th>
                        <th class="report-th text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($buckets as $label => $count)
                    <tr>
                        <td class="report-td font-medium">{{ $label }}</td>
                        <td class="report-td text-right font-bold">{{ $count }}</td>
                        <td class="report-td text-right text-gray-600">
                            {{ $patientsWithMultiple > 0 ? round(($count / $patientsWithMultiple) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="mt-4 text-xs text-gray-400 print:text-gray-500">
                Frecuencia calculada como promedio de días entre visitas consecutivas de cada paciente. Solo pacientes con 2 o más visitas en el período.
            </p>

        </div>
    </div>
</div>
@endsection
