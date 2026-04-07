@extends('layouts.print')

@section('title', 'Cobros Pendientes – ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.cobros-pendientes', request()->query()))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Cobros Pendientes"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen</h4>
            <div class="grid grid-cols-2 gap-3 mb-6 print:gap-1 print:mb-3">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-yellow-700">Turnos Pendientes</span>
                        <span class="text-sm font-bold text-yellow-700">{{ number_format($totals['count']) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-red-700">Monto Estimado</span>
                        <span class="text-sm font-bold text-red-700">${{ number_format($totals['estimated_total'], 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            @if($byProfessional->isNotEmpty())
            <h4 class="report-section-title">Por Profesional</h4>
            <table class="report-table w-full mb-4">
                <thead>
                    <tr>
                        <th class="report-th text-left">Profesional</th>
                        <th class="report-th text-right">Turnos</th>
                        <th class="report-th text-right">Monto Est.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byProfessional as $row)
                    <tr>
                        <td class="report-td font-medium">{{ $row['name'] }}</td>
                        <td class="report-td text-right font-bold text-yellow-700">{{ $row['count'] }}</td>
                        <td class="report-td text-right font-bold text-red-700">${{ number_format($row['amount'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <h4 class="report-section-title">Detalle de Turnos</h4>
            <table class="report-table w-full">
                <thead>
                    <tr>
                        <th class="report-th text-left">Fecha</th>
                        <th class="report-th text-left">Paciente</th>
                        <th class="report-th text-left">Profesional</th>
                        <th class="report-th text-right">Monto Est.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pending as $apt)
                    <tr>
                        <td class="report-td">{{ \Carbon\Carbon::parse($apt->appointment_date)->format('d/m/Y') }}</td>
                        <td class="report-td font-medium">{{ $apt->patient?->full_name ?? '—' }}</td>
                        <td class="report-td text-gray-600">{{ $apt->professional?->full_name ?? '—' }}</td>
                        <td class="report-td text-right font-bold text-red-700">${{ number_format($apt->estimated_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
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
