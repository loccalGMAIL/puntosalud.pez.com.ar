@extends('layouts.print')

@section('title', 'Liquidaciones Históricas - ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.liquidaciones-historicas'))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <!-- Encabezado -->
        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Liquidaciones Históricas"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}{{ $professionalName ? ' · ' . $professionalName : '' }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <!-- Resumen -->
            <h4 class="report-section-title">Resumen del Período</h4>
            <div class="grid grid-cols-4 gap-3 mb-4 print:gap-1 print:mb-2">
                @foreach([
                    ['label' => 'Liquidaciones', 'value' => $totals['count'], 'prefix' => ''],
                    ['label' => 'Total Facturado', 'value' => number_format($totals['total_collected'], 2), 'prefix' => '$'],
                    ['label' => 'Para Profesionales', 'value' => number_format($totals['professional_amount'], 2), 'prefix' => '$'],
                    ['label' => 'Para Clínica', 'value' => number_format($totals['clinic_amount'], 2), 'prefix' => '$'],
                ] as $kpi)
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700 print:text-gray-800">{{ $kpi['label'] }}</span>
                        <span class="text-sm font-bold text-gray-900 print:text-black">{{ $kpi['prefix'] }}{{ $kpi['value'] }}</span>
                    </p>
                </div>
                @endforeach
            </div>

            <!-- Detalle por mes -->
            @foreach($byMonth as $month => $monthLiquidations)
            @php
                $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMMM YYYY');
            @endphp
            <h5 class="report-section-title capitalize">{{ $monthLabel }}</h5>
            <table class="w-full text-[11px] print:text-[9px] border-collapse mb-3 print:mb-1">
                <thead>
                    <tr class="border-b border-gray-300 print:border-gray-400">
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:12%">Fecha</th>
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:28%">Profesional</th>
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:20%">Especialidad</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:15%">Facturado</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:13%">Para Prof.</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:12%">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 print:divide-gray-300">
                    @foreach($monthLiquidations as $liq)
                    <tr>
                        <td class="py-[1px] px-1 print:text-black">{{ $liq->liquidation_date->format('d/m/Y') }}</td>
                        <td class="py-[1px] px-1 font-medium print:text-black">{{ $liq->professional->full_name }}</td>
                        <td class="py-[1px] px-1 print:text-gray-700">{{ $liq->professional->specialty?->name ?? '—' }}</td>
                        <td class="py-[1px] px-1 text-right print:text-black">${{ number_format($liq->total_collected, 2) }}</td>
                        <td class="py-[1px] px-1 text-right print:text-black">${{ number_format($liq->net_professional_amount, 2) }}</td>
                        <td class="py-[1px] px-1 text-center print:text-black">{{ $liq->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-400">
                        <td colspan="3" class="py-[1px] px-1 font-bold print:text-black">Subtotal {{ ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMM YY')) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($monthLiquidations->sum('total_collected'), 2) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($monthLiquidations->sum('net_professional_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            @endforeach

        </div>

        <!-- Pie -->
        <div class="p-4 border-t border-gray-200 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ auth()->user()->name }}</p>
                <p>Total: ${{ number_format($totals['total_collected'], 2) }} · {{ $totals['count'] }} liquidaciones</p>
            </div>
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
