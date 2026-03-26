@extends('layouts.print')

@section('title', 'Comisiones por Profesional - ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.profesionales.comisiones'))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Profesionales – Comisiones"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <h4 class="report-section-title">Resumen</h4>
            <div class="grid grid-cols-3 gap-3 mb-4 print:gap-1 print:mb-2">
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Total Facturado</span>
                        <span class="text-sm font-bold text-gray-900">${{ number_format($totals['total_collected'], 2) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Para Profesionales</span>
                        <span class="text-sm font-bold text-gray-900">${{ number_format($totals['professional_amount'], 2) }}</span>
                    </p>
                </div>
                <div class="bg-gray-50 p-2 rounded print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-1">
                        <span class="text-[10px] font-medium text-gray-700">Para Clínica</span>
                        <span class="text-sm font-bold text-gray-900">${{ number_format($totals['clinic_amount'], 2) }}</span>
                    </p>
                </div>
            </div>

            <h5 class="report-section-title">Detalle por Profesional</h5>
            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                <thead>
                    <tr class="border-b border-gray-300 print:border-gray-400">
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:30%">Profesional</th>
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:20%">Especialidad</th>
                        <th class="text-center py-[2px] px-1 font-semibold print:text-black" style="width:8%">Com.%</th>
                        <th class="text-center py-[2px] px-1 font-semibold print:text-black" style="width:8%">Liq.</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:16%">Facturado</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:16%">Comisión</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:12%">Clínica</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 print:divide-gray-300">
                    @foreach($byProfessional as $p)
                    <tr>
                        <td class="py-[1px] px-1 font-medium print:text-black">{{ $p['full_name'] }}</td>
                        <td class="py-[1px] px-1 print:text-gray-700">{{ $p['specialty'] }}</td>
                        <td class="py-[1px] px-1 text-center print:text-gray-700">{{ $p['commission_pct'] }}%</td>
                        <td class="py-[1px] px-1 text-center print:text-black">{{ $p['liquidations_count'] }}</td>
                        <td class="py-[1px] px-1 text-right print:text-black">${{ number_format($p['total_collected'], 2) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($p['professional_amount'], 2) }}</td>
                        <td class="py-[1px] px-1 text-right print:text-black">${{ number_format($p['clinic_amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-400">
                        <td colspan="4" class="py-[1px] px-1 font-bold print:text-black">TOTAL</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($totals['total_collected'], 2) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($totals['professional_amount'], 2) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($totals['clinic_amount'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>

        </div>

        <div class="p-4 border-t border-gray-200 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ auth()->user()->name }}</p>
                <p>{{ $byProfessional->count() }} profesionales · Comisión total: ${{ number_format($totals['professional_amount'], 2) }}</p>
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
