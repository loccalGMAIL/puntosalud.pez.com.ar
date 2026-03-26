@extends('layouts.print')

@section('title', 'Comparativa de Profesionales - ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.profesionales.comparativa'))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <div class="p-2 border-b border-gray-200 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Profesionales – Comparativa"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">
            <h5 class="report-section-title">Comparativa por Profesional</h5>
            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                <thead>
                    <tr class="border-b border-gray-300 print:border-gray-400">
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:28%">Profesional</th>
                        <th class="text-left py-[2px] px-1 font-semibold print:text-black" style="width:18%">Especialidad</th>
                        <th class="text-center py-[2px] px-1 font-semibold print:text-black" style="width:8%">Com.%</th>
                        <th class="text-center py-[2px] px-1 font-semibold print:text-black" style="width:8%">Turnos</th>
                        <th class="text-center py-[2px] px-1 font-semibold print:text-black" style="width:8%">Atendidos</th>
                        <th class="text-center py-[2px] px-1 font-semibold print:text-black" style="width:8%">Asistencia</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:14%">Facturado</th>
                        <th class="text-right py-[2px] px-1 font-semibold print:text-black" style="width:14%">Comisión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 print:divide-gray-300">
                    @foreach($professionals as $p)
                    <tr>
                        <td class="py-[1px] px-1 font-medium print:text-black">{{ $p['full_name'] }}</td>
                        <td class="py-[1px] px-1 print:text-gray-700">{{ $p['specialty'] }}</td>
                        <td class="py-[1px] px-1 text-center print:text-gray-700">{{ $p['commission_pct'] }}%</td>
                        <td class="py-[1px] px-1 text-center print:text-black">{{ $p['appointments'] }}</td>
                        <td class="py-[1px] px-1 text-center print:text-black">{{ $p['attended'] }}</td>
                        <td class="py-[1px] px-1 text-center print:text-black">{{ $p['attendance_rate'] }}%</td>
                        <td class="py-[1px] px-1 text-right print:text-black">${{ number_format($p['billed_total'], 2) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($p['commission_total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-400">
                        <td colspan="3" class="py-[1px] px-1 font-bold print:text-black">TOTAL</td>
                        <td class="py-[1px] px-1 text-center font-bold print:text-black">{{ $professionals->sum('appointments') }}</td>
                        <td class="py-[1px] px-1 text-center font-bold print:text-black">{{ $professionals->sum('attended') }}</td>
                        <td></td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($professionals->sum('billed_total'), 2) }}</td>
                        <td class="py-[1px] px-1 text-right font-bold print:text-black">${{ number_format($professionals->sum('commission_total'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ auth()->user()->name }}</p>
                <p>{{ $professionals->count() }} profesionales</p>
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
