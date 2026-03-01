@extends('layouts.print')

@section('title', 'Movimientos de Caja - ' . $cashSummary['date']->format('d/m/Y'))
@section('back-url', route('reports.cash', ['date' => $cashSummary['date']->format('Y-m-d')]))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">

        <!-- Encabezado del Reporte -->
        <div class="p-2 border-b border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Movimientos de Caja"
                :subtitle="$cashSummary['date']->translatedFormat('l, d \d\e F \d\e Y')"
            />
        </div>

        <div class="p-6 print:p-2">

            <!-- Resumen -->
            <h4 class="report-section-title">Resumen</h4>
            <div class="grid grid-cols-4 gap-4 mb-6 print:gap-1 print:mb-2">
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-gray-700 dark:text-gray-200 print:text-gray-800">Saldo Inicial</span>
                        <span class="text-base font-bold text-gray-900 dark:text-white print:text-black print:text-sm">${{ number_format($cashSummary['initial_balance'], 2, ',', '.') }}</span>
                    </p>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-green-900 dark:text-green-200 print:text-gray-800">Ingresos</span>
                        <span class="text-base font-bold text-green-600 dark:text-green-400 print:text-black print:text-sm">+${{ number_format($cashSummary['total_inflows'], 2, ',', '.') }}</span>
                    </p>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-red-900 dark:text-red-200 print:text-gray-800">Egresos</span>
                        <span class="text-base font-bold text-red-600 dark:text-red-400 print:text-black print:text-sm">-${{ number_format($cashSummary['total_outflows'], 2, ',', '.') }}</span>
                    </p>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-blue-900 dark:text-blue-200 print:text-gray-800">Saldo Final</span>
                        <span class="text-base font-bold text-blue-700 dark:text-blue-300 print:text-black print:text-sm">${{ number_format($cashSummary['final_balance'], 2, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <!-- Tabla de Movimientos -->
            @if($movements->count() > 0)
            <div class="mb-2 print:mb-0.5">
                <h5 class="report-section-title">Movimientos del Día ({{ $movements->count() }})</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] print:text-[9px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:5%">ID</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:6%">Hora</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:15%">Tipo</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:10%">Usuario</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:32%">Concepto</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:12%">Método</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:10%">Monto</th>
                                <th class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:10%">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                            @foreach($movements as $movement)
                            <tr>
                                <td class="py-[1px] px-1 text-gray-500 dark:text-gray-400 print:text-gray-600 font-mono">#{{ $movement->id }}</td>
                                <td class="py-[1px] px-1 text-gray-600 dark:text-gray-400 print:text-gray-700">{{ $movement->created_at->format('H:i') }}</td>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                    {{ $movement->movementType?->icon }} {{ $movement->movementType?->name ?? 'Desconocido' }}
                                </td>
                                <td class="py-[1px] px-1 text-gray-700 dark:text-gray-300 print:text-gray-700">{{ $movement->user?->name ?? 'Sistema' }}</td>
                                <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                    @if($movement->movementType?->code === 'professional_payment' && $movement->reference_type === 'App\\Models\\Professional' && $movement->reference_id)
                                        @php $professional = \App\Models\Professional::find($movement->reference_id); @endphp
                                        @if($professional)
                                            {{ $movement->description }} - Dr. {{ $professional->first_name }} {{ $professional->last_name }}
                                        @else
                                            {{ $movement->description }}
                                        @endif
                                    @else
                                        {{ $movement->description }}
                                    @endif
                                </td>
                                <td class="py-[1px] px-1 text-gray-700 dark:text-gray-300 print:text-gray-700">
                                    @if($movement->reference_type === 'App\\Models\\Payment' && $movement->reference)
                                        @php
                                            $methodLabels = [
                                                'cash' => 'Efectivo',
                                                'transfer' => 'Transfer.',
                                                'debit_card' => 'Débito',
                                                'credit_card' => 'Crédito',
                                                'qr' => 'QR',
                                            ];
                                            $paymentMethods = $movement->reference->paymentDetails
                                                ->pluck('payment_method')
                                                ->map(fn($method) => $methodLabels[$method] ?? ucfirst($method))
                                                ->unique();
                                        @endphp
                                        {{ $paymentMethods->join(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-[1px] px-1 text-right font-medium {{ $movement->amount > 0 ? 'text-green-600 dark:text-green-400 print:text-green-700' : 'text-red-600 dark:text-red-400 print:text-red-700' }}">
                                    @if($movement->amount > 0)
                                        +${{ number_format($movement->amount, 2, ',', '.') }}
                                    @else
                                        ${{ number_format($movement->amount, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="py-[1px] px-1 text-right font-mono text-gray-900 dark:text-white print:text-black">${{ number_format($movement->balance_after, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-500 print:border-gray-500">
                                <td colspan="6" class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">TOTALES</td>
                                <td class="py-[1px] px-1 text-right font-bold">
                                    <span class="text-green-600 dark:text-green-400 print:text-green-700">+${{ number_format($cashSummary['total_inflows'], 2, ',', '.') }}</span>
                                    <br>
                                    <span class="text-red-600 dark:text-red-400 print:text-red-700">-${{ number_format($cashSummary['total_outflows'], 2, ',', '.') }}</span>
                                </td>
                                <td class="py-[1px] px-1 text-right font-bold font-mono text-gray-900 dark:text-white print:text-black">${{ number_format($cashSummary['final_balance'], 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400 print:text-gray-600">No hay movimientos registrados para este día.</p>
            @endif

        </div>

        <!-- Pie del Reporte -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ auth()->user()->name }}</p>
                <p>Total de movimientos: {{ $movements->count() }}</p>
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
