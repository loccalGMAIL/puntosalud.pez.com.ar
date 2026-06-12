@extends('layouts.print')

@section('title', 'Ocupación de Consultorios – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.pacientes.consultorios', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Pacientes – Ocupación de Consultorios"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen Global</h4>
            <div class="grid grid-cols-4 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Consultorios</span>
                        <span class="text-sm font-bold text-gray-900">{{ $stats->count() }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total Turnos</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($globalTotal) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-blue-700">Total Horas</span>
                        <span class="text-sm font-bold text-blue-700">{{ number_format($globalHours, 1) }} h</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Tasa Asistencia</span>
                        <span class="text-sm font-bold text-gray-900">{{ $globalRate !== null ? $globalRate . '%' : '—' }}</span>
                    </p>
                </div>
            </div>

            <h4 class="report-section-title">Detalle por Consultorio</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Consultorio</th>
                        <th class="report-th text-center">Atendidos</th>
                        <th class="report-th text-center">Ausentes</th>
                        <th class="report-th text-center">Cancelados</th>
                        <th class="report-th text-center">Pendientes</th>
                        <th class="report-th text-center">Total</th>
                        <th class="report-th text-center">Horas</th>
                        <th class="report-th text-center">Tasa Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats as $s)
                    <tr>
                        <td class="report-td font-medium">{{ $s['office_name'] }}</td>
                        <td class="report-td text-center text-emerald-700">{{ $s['attended'] }}</td>
                        <td class="report-td text-center text-yellow-700">{{ $s['absent'] }}</td>
                        <td class="report-td text-center text-red-600">{{ $s['cancelled'] }}</td>
                        <td class="report-td text-center text-blue-600">{{ $s['scheduled'] }}</td>
                        <td class="report-td text-center font-bold">{{ $s['total'] }}</td>
                        <td class="report-td text-center font-medium text-blue-700">{{ number_format($s['total_hours'], 1) }} h</td>
                        <td class="report-td text-center font-bold {{ $s['attendance_rate'] !== null && $s['attendance_rate'] >= 80 ? 'text-emerald-700' : ($s['attendance_rate'] !== null && $s['attendance_rate'] >= 60 ? 'text-yellow-700' : 'text-red-700') }}">
                            {{ $s['attendance_rate'] !== null ? $s['attendance_rate'] . '%' : '—' }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-400 font-bold bg-gray-50">
                        <td class="report-td font-bold">TOTAL</td>
                        <td class="report-td text-center font-bold text-emerald-700">{{ $globalAttended }}</td>
                        <td class="report-td text-center font-bold text-yellow-700">{{ $globalAbsent }}</td>
                        <td class="report-td text-center font-bold text-red-600">{{ $stats->sum('cancelled') }}</td>
                        <td class="report-td text-center font-bold text-blue-600">{{ $stats->sum('scheduled') }}</td>
                        <td class="report-td text-center font-bold">{{ $globalTotal }}</td>
                        <td class="report-td text-center font-bold text-blue-700">{{ number_format($globalHours, 1) }} h</td>
                        <td class="report-td text-center font-bold">{{ $globalRate !== null ? $globalRate . '%' : '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <h4 class="report-section-title mt-6 print:mt-3">Horas por profesional</h4>
            @foreach($stats as $s)
            <div class="mb-4 print:mb-2">
                <p class="text-xs font-semibold text-gray-700 mb-1">
                    {{ $s['office_name'] }}
                    <span class="font-normal text-blue-700 ml-1">— {{ number_format($s['total_hours'], 1) }} h · {{ $s['total'] }} turnos</span>
                </p>
                <table class="report-table w-full">
                    <thead>
                        <tr>
                            <th class="report-th text-left">Profesional</th>
                            <th class="report-th text-center">Turnos</th>
                            <th class="report-th text-center">Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($s['by_professional'] as $p)
                        <tr>
                            <td class="report-td">{{ $p['name'] }}</td>
                            <td class="report-td text-center">{{ $p['total'] }}</td>
                            <td class="report-td text-center font-medium text-blue-700">{{ number_format($p['hours'], 1) }} h</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach

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
