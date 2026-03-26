@extends('layouts.print')

@section('title', 'Consultas por Profesional - ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.profesionales.consultas', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Profesionales – Consultas"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen Global</h4>
            <div class="grid grid-cols-5 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total Turnos</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($globalTotal) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Atendidos</span>
                        <span class="text-sm font-bold text-emerald-700">{{ number_format($globalAttended) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-yellow-700">Ausentes</span>
                        <span class="text-sm font-bold text-yellow-700">{{ number_format($globalAbsent) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-red-700">Cancelados</span>
                        <span class="text-sm font-bold text-red-700">{{ number_format($globalCancelled) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-emerald-700">Tasa Asistencia</span>
                        <span class="text-sm font-bold text-emerald-700">{{ $globalRate }}%</span>
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
                        <th class="report-th text-center">Cancelados</th>
                        <th class="report-th text-center">Pendientes</th>
                        <th class="report-th text-center">Total</th>
                        <th class="report-th text-center">Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($professionals as $p)
                    <tr>
                        <td class="report-td font-medium">{{ $p['full_name'] }}</td>
                        <td class="report-td text-gray-600">{{ $p['specialty'] }}</td>
                        <td class="report-td text-center font-medium text-emerald-700">{{ $p['attended'] }}</td>
                        <td class="report-td text-center font-medium text-yellow-700">{{ $p['absent'] }}</td>
                        <td class="report-td text-center font-medium text-red-700">{{ $p['cancelled'] }}</td>
                        <td class="report-td text-center text-blue-700">{{ $p['scheduled'] }}</td>
                        <td class="report-td text-center font-bold">{{ $p['total'] }}</td>
                        <td class="report-td text-center font-bold {{ $p['attendance_rate'] >= 80 ? 'text-emerald-700' : ($p['attendance_rate'] >= 60 ? 'text-yellow-700' : 'text-red-700') }}">
                            {{ $p['attendance_rate'] }}%
                        </td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold" colspan="2">TOTAL</td>
                        <td class="report-td text-center font-bold text-emerald-700">{{ $globalAttended }}</td>
                        <td class="report-td text-center font-bold text-yellow-700">{{ $globalAbsent }}</td>
                        <td class="report-td text-center font-bold text-red-700">{{ $globalCancelled }}</td>
                        <td class="report-td text-center font-bold text-blue-700">{{ $globalScheduled }}</td>
                        <td class="report-td text-center font-bold">{{ $globalTotal }}</td>
                        <td class="report-td text-center font-bold text-emerald-700">{{ $globalRate }}%</td>
                    </tr>
                </tbody>
            </table>

            <p class="mt-4 text-xs text-gray-400 print:text-gray-500">
                Tasa de asistencia calculada sobre turnos completados (atendidos + ausentes). Pendientes = turnos aún programados.
            </p>

        </div>
    </div>
</div>
@endsection
