@extends('layouts.print')

@section('title', 'Informe de Gastos - ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))
@section('back-url', route('reports.expenses'))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">

        <!-- Encabezado del Reporte -->
        <div class="p-2 border-b border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Informe de Gastos"
                subtitle="Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}"
            />
        </div>

        <div class="p-6 print:p-2">

            <!-- Resumen -->
            <h4 class="report-section-title">Resumen</h4>
            <div class="grid grid-cols-4 gap-4 mb-6 print:gap-1 print:mb-2">
                <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-red-900 dark:text-red-200 print:text-gray-800">Total Gastos</span>
                        <span class="text-base font-bold text-red-600 dark:text-red-400 print:text-black print:text-sm">-${{ number_format($totalAmount, 2) }}</span>
                    </p>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-blue-900 dark:text-blue-200 print:text-gray-800">Registros</span>
                        <span class="text-base font-bold text-blue-600 dark:text-blue-400 print:text-black print:text-sm">{{ $totalCount }}</span>
                    </p>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-purple-900 dark:text-purple-200 print:text-gray-800">Tipos de Gasto</span>
                        <span class="text-base font-bold text-purple-600 dark:text-purple-400 print:text-black print:text-sm">{{ $byType->count() }}</span>
                    </p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-gray-900 dark:text-gray-200 print:text-gray-800">Promedio</span>
                        <span class="text-base font-bold text-gray-700 dark:text-gray-300 print:text-black print:text-sm">-${{ number_format($avgAmount, 2) }}</span>
                    </p>
                </div>
            </div>

            <!-- Desglose por Tipo -->
            @if($byType->count() > 0)
            <div class="mb-2 print:mb-0.5">
                <h5 class="report-section-title">Desglose por Tipo de Gasto</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] print:text-[9px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:55%">Tipo</th>
                                <th class="text-center py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:20%">Cantidad</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:25%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                            @foreach($byType as $item)
                            <tr>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">{{ $item['name'] }}</td>
                                <td class="py-[1px] px-1 text-center text-gray-600 dark:text-gray-400 print:text-gray-700">{{ $item['count'] }}</td>
                                <td class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">-${{ number_format($item['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-500 print:border-gray-500">
                                <td class="py-[1px] px-1 font-bold text-gray-900 dark:text-white print:text-black">TOTAL</td>
                                <td class="py-[1px] px-1 text-center font-bold text-gray-900 dark:text-white print:text-black">{{ $totalCount }}</td>
                                <td class="py-[1px] px-1 text-right font-bold text-red-600 dark:text-red-400 print:text-red-700">-${{ number_format($totalAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            <!-- Detalle de Gastos -->
            @if($movements->count() > 0)
            <div class="mb-2 print:mb-0.5">
                <h5 class="report-section-title">Detalle de Gastos ({{ $totalCount }} registros)</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] print:text-[9px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:13%">Fecha</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:8%">Hora</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:22%">Tipo</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:37%">Descripción</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:12%">Monto</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:8%">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                            @foreach($movements as $m)
                            <tr>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">{{ $m->created_at->format('d/m/Y') }}</td>
                                <td class="py-[1px] px-1 text-gray-600 dark:text-gray-400 print:text-gray-700">{{ $m->created_at->format('H:i') }}</td>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">{{ $m->movementType?->name ?? '-' }}</td>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">{{ $m->description ?: '-' }}</td>
                                <td class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">-${{ number_format(abs($m->amount), 2) }}</td>
                                <td class="py-[1px] px-1 text-gray-600 dark:text-gray-400 print:text-gray-700">{{ $m->user?->name ?? 'Sis.' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        <!-- Pie del Reporte -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ auth()->user()->name }}</p>
                <p>Total: -${{ number_format($totalAmount, 2) }} · {{ $totalCount }} registros</p>
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
