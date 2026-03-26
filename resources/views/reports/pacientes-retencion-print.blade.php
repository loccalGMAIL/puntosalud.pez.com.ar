@extends('layouts.print')

@section('title', 'Retención de Pacientes – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.pacientes.retencion', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Pacientes – Retención"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Indicadores de Retención</h4>
            <div class="grid grid-cols-4 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Pacientes Únicos</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($totalUnique) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Volvieron</span>
                        <span class="text-sm font-bold text-emerald-700">{{ number_format($returning) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Solo 1 Visita</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($singleVisit) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Tasa Retención</span>
                        <span class="text-sm font-bold text-emerald-700">{{ $retentionRate }}%</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-blue-700">Pacientes Nuevos</span>
                        <span class="text-sm font-bold text-blue-700">{{ number_format($newPatients) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-purple-700">Recurrentes</span>
                        <span class="text-sm font-bold text-purple-700">{{ number_format($recurringPatients) }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Distribución de Visitas por Paciente</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Nº de Visitas</th>
                        <th class="report-th text-right">Pacientes</th>
                        <th class="report-th text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visitDistribution as $visits => $count)
                    <tr>
                        <td class="report-td">{{ $visits }} {{ $visits == 1 ? 'visita' : 'visitas' }}</td>
                        <td class="report-td text-right font-bold">{{ $count }}</td>
                        <td class="report-td text-right text-gray-600">
                            {{ $totalUnique > 0 ? round(($count / $totalUnique) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
