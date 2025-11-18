@extends('layouts.print')

@section('title', 'Reporte de Cierre del Día - ' . $selectedDate->format('d/m/Y'))
@section('mobileTitle', 'Reporte Diario')

@section('content')
    <div class="p-6 print:p-1" x-data="dailyReportForm()">

        <!-- Contenido del Reporte -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">
            <!-- Encabezado del Reporte -->
            <div class="p-2 border-b border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-0.5">
                <div class="flex items-center justify-between gap-2">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <img src="{{ asset('logo.png') }}" alt="Logo PuntoSalud"
                            class="w-32 h-32 print:w-24 print:h-24 object-contain">
                    </div>

                    <!-- Información del Reporte -->
                    <div class="flex-1 text-center space-y-0.5">
                        <h3
                            class="text-base font-semibold text-gray-700 dark:text-gray-300 print:text-gray-700 print:text-sm">
                            Reporte de Cierre de Caja</h3>
                        <p class="text-gray-600 dark:text-gray-400 print:text-gray-700 print:text-xs">
                            {{ $selectedDate->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 print:text-gray-500 print:text-xs">Generado:
                            {{ now()->format('d/m/Y H:i') }}</p>
                    </div>

                    <!-- Espacio para balance visual -->
                    <div class="flex-shrink-0 w-32 print:w-24"></div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="p-6 print:p-2">
                <h4 class="report-section-title">Resumen Financiero</h4>

                <div class="grid grid-cols-4 gap-4 mb-6 print:gap-1 print:mb-2">
                    <div
                        class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                        <p class="flex items-baseline justify-between gap-2">
                            <span
                                class="text-[10px] font-medium text-green-900 dark:text-green-200 print:text-gray-800">Ingresos</span>
                            <span
                                class="text-base font-bold text-green-600 dark:text-green-400 print:text-black print:text-sm">+${{ number_format($summary['total_inflows'], 2) }}</span>
                        </p>
                    </div>

                    <div
                        class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                        <p class="flex items-baseline justify-between gap-2">
                            <span
                                class="text-[10px] font-medium text-red-900 dark:text-red-200 print:text-gray-800">Egresos</span>
                            <span
                                class="text-base font-bold text-red-600 dark:text-red-400 print:text-black print:text-sm">-${{ number_format($summary['total_outflows'], 2) }}</span>
                        </p>
                    </div>

                    <div
                        class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                        <p class="flex items-baseline justify-between gap-2">
                            <span
                                class="text-[10px] font-medium text-purple-900 dark:text-purple-200 print:text-gray-800">Final
                                Teórico</span>
                            <span
                                class="text-base font-bold text-purple-600 dark:text-purple-400 print:text-black print:text-sm">${{ number_format($summary['final_balance'], 2) }}</span>
                        </p>
                    </div>

                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg print:bg-yellow-100 print:border print:border-yellow-400 print:p-1">
                        <p class="flex items-baseline justify-between gap-2">
                            <span
                                class="text-[10px] font-medium text-blue-900 dark:text-blue-200 print:text-gray-800">Saldo Final</span>
                            <span
                                class="text-base font-bold text-blue-600 dark:text-blue-400 print:text-black print:text-sm">${{ number_format($summary['final_balance_with_zalazar'], 2) }}</span>
                        </p>
                    </div>
                </div>

                @if ($summary['closing_movement'])
                    <!-- Estado de Cierre -->
<div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg mb-4 print:bg-gray-100 print:border print:border-gray-300 print:p-1 print:mb-2">
    <div class="text-[11px] print:text-[9px] space-y-1">
        <div>
            <span class="font-semibold text-gray-900 dark:text-white print:text-black">Cerrado por:</span>
            <span class="text-gray-800 dark:text-gray-200 print:text-gray-800 ml-1">
                {{ $summary['closing_movement']?->user?->name ?? 'N/A' }}
            </span>
        </div>

        @php
            $description = $summary['closing_movement']->description ?? '';
            $notes = trim(preg_replace('/^Cierre de caja\s*-\s*/i', '', $description));
            if ($notes === '' || $notes === $description) {
                $notes = null;
            }
        @endphp

        @if ($notes)
            <div>
                <span class="font-semibold text-gray-900 dark:text-white print:text-black">Observaciones:</span>
                <span class="text-gray-800 dark:text-gray-200 print:text-gray-800 ml-1">
                    {{ $notes }}
                </span>
            </div>
        @endif
    </div>
</div>


                @endif

                <!-- Desglose por Tipo de Movimiento -->
                @if ($movementsByType->count() > 0)
                    <div class="mb-2 print:mb-0.5">
                        <h5 class="report-section-title">
                            Desglose por Tipo de Movimiento
                        </h5>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                        <th
                                            class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Tipo</th>
                                        <th
                                            class="text-center py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Cant.</th>
                                        <th
                                            class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Ingresos</th>
                                        <th
                                            class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Egresos</th>
                                        <th
                                            class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Neto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                                    @foreach ($movementsByType as $type => $data)
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $data['icon'] ?? '' }}
                                                {{ $data['type_name'] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-center text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                {{ $data['count'] }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-right text-green-600 dark:text-green-400 print:text-green-700">
                                                +${{ number_format($data['inflows'], 2) }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">
                                                -${{ number_format($data['outflows'], 2) }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-right font-medium text-gray-900 dark:text-white print:text-black">
                                                ${{ number_format($data['inflows'] - $data['outflows'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                @endif

                <!-- Detalle de Movimientos -->
                @php
                    $professionalModulePayments = $movements->filter(
                        fn($m) => $m->movementType?->code === 'professional_module_payment',
                    );
                    $expenses = $movements->filter(fn($m) => $m->movementType?->code === 'expense');
                    $otherOutflows = $movements->filter(
                        fn($m) => !in_array($m->movementType?->code, [
                            'cash_opening',
                            'cash_closing',
                            'professional_payment',
                            'expense',
                            'patient_payment',
                            'professional_module_payment',
                        ]) && $m->amount < 0,
                    );
                @endphp

                @if ($professionalModulePayments->count() > 0)
                    <div class="mb-2 print:mb-0.5">
                        <h5 class="report-section-title">💳 Detalle de Pagos Módulo Profesional</h5>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                        <th
                                            class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Profesional</th>
                                        <th
                                            class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Concepto</th>
                                        <th
                                            class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                                    @foreach ($professionalModulePayments as $movement)
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">"
                                                @if ($movement->reference && get_class($movement->reference) === 'App\Models\Professional')
                                                    Dr. {{ $movement->reference->first_name }}
                                                    {{ $movement->reference->last_name }}
                                                @else
                                                    {{ preg_match('/Dr\.\s+([^-]+)/', $movement->description, $matches) ? trim($matches[1]) : 'N/A' }}
                                                @endif
                                            </td>
                                            <td class="py-[1px] px-1 text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                {{ preg_replace('/^\[.*?\]\s*|^Pago Módulo - Dr\.\s+[^-]+-\s*/', '', $movement->description) }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-right text-green-600 dark:text-green-400 print:text-green-700">
                                                +${{ number_format($movement->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($expenses->count() > 0)
                    <div class="mb-2 print:mb-0.5">
                        <h5 class="report-section-title">📤 Detalle de Gastos</h5>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                        <th
                                            class="text-left py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Hora</th>
                                        <th
                                            class="text-left py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Descripción</th>
                                        <th
                                            class="text-right py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                                    @foreach ($expenses as $movement)
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $movement->created_at->format('H:i') }}</td>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $movement->description }}</td>
                                            <td
                                                class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">
                                                -${{ number_format(abs($movement->amount), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($otherOutflows->count() > 0)
                    <div class="mb-2 print:mb-0.5">
                        <h5 class="report-section-title">📋 Otros Egresos</h5>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                        <th
                                            class="text-left py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Hora</th>
                                        <th
                                            class="text-left py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Tipo</th>
                                        <th
                                            class="text-left py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Descripción</th>
                                        <th
                                            class="text-right py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                                    @foreach ($otherOutflows as $movement)
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $movement->created_at->format('H:i') }}</td>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $movement->movementType?->name ?? 'N/A' }}</td>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $movement->description }}</td>
                                            <td
                                                class="py-[1px] px-1 text-right text-red-600 dark:text-red-400 print:text-red-700">
                                                -${{ number_format(abs($movement->amount), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Liquidación por Profesional -->
                @if ($professionalIncome->count() > 0)
                    @php
                        $methodConfig = [
                            'cash' => ['emoji' => '💵', 'label' => 'Efectivo'],
                            'transfer' => ['emoji' => '🏦', 'label' => 'Transf.'],
                            'debit_card' => ['emoji' => '💳', 'label' => 'Débito'],
                            'credit_card' => ['emoji' => '💳', 'label' => 'Crédito'],
                            'qr' => ['emoji' => '📱', 'label' => 'QR'],
                        ];
                    @endphp
                    <div class="mb-2 print:mb-0.5">
                        <h5 class="report-section-title">👨‍⚕️Liquidación por Profesional</h5>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                        <th
                                            class="text-left py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Profesional</th>
                                        <th
                                            class="text-center py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Cons.</th>
                                        @foreach($activePaymentMethods as $method)
                                            <th
                                                class="text-right py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                                {{ $methodConfig[$method]['emoji'] ?? strtoupper($method) }}</th>
                                        @endforeach
                                        <th
                                            class="text-right py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Total</th>
                                        <th
                                            class="text-right py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Prof.</th>
                                        <th
                                            class="text-right py-[1px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Clínica</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                                    @foreach ($professionalIncome as $data)
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                {{ $data['full_name'] }}</td>
                                            <td
                                                class="py-[1px] px-1 text-center text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                {{ $data['count'] }}</td>
                                            @foreach($activePaymentMethods as $method)
                                                <td class="py-[1px] px-1 text-right text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                    @if($data[$method] > 0)${{ number_format($data[$method], 0) }}@else-@endif</td>
                                            @endforeach
                                            <td
                                                class="py-[1px] px-1 text-right font-medium text-gray-900 dark:text-white print:text-black">
                                                ${{ number_format($data['total_collected'], 0) }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-right font-medium text-blue-600 dark:text-blue-400 print:text-blue-700">
                                                ${{ number_format($data['professional_amount'], 0) }}
                                            </td>
                                            <td
                                                class="py-[1px] px-1 text-right font-medium text-green-600 dark:text-green-400 print:text-green-700">
                                                ${{ number_format($data['clinic_amount'], 0) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="border-t-2 border-gray-300 dark:border-gray-500 print:border-gray-500">
                                        <td colspan="2"
                                            class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                            TOTALES:</td>
                                        @foreach($activePaymentMethods as $method)
                                            <td class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                                ${{ number_format($professionalIncome->sum($method), 0) }}</td>
                                        @endforeach
                                        <td
                                            class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                            ${{ number_format($professionalIncome->sum('total_collected'), 0) }}
                                        </td>
                                        <td
                                            class="py-[1px] px-1 text-right font-bold text-blue-600 dark:text-blue-400 print:text-blue-700">
                                            ${{ number_format($professionalIncome->sum('professional_amount'), 0) }}
                                        </td>
                                        <td
                                            class="py-[1px] px-1 text-right font-bold text-green-600 dark:text-green-400 print:text-green-700">
                                            ${{ number_format($professionalIncome->sum('clinic_amount'), 0) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Desglose de Ingresos Dra. Zalazar -->
                @php
                    $zalazarData = $professionalIncome->firstWhere('professional_id', 1);
                @endphp
                @if ($zalazarData || $zalazarBalancePayments->count() > 0)
                    <div class="mb-2 print:mb-0.5">
                        <h5 class="report-section-title">💰 Ingresos Dra. Natalia Zalazar</h5>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[11px] print:text-[9px] border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                        <th
                                            class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Concepto</th>
                                            <th>Detalle</th>
                                        @foreach($activePaymentMethods as $method)
                                            <th
                                                class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                                {{ $methodConfig[$method]['emoji'] ?? strtoupper($method) }}</th>
                                        @endforeach
                                        <th
                                            class="text-right py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                                    @if ($zalazarData)
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                Facturación de Pacientes</td>
                                                <td class="py-[1px] px-1 text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                    {{ $zalazarData['count'] }} consulta{{ $zalazarData['count'] != 1 ? 's' : '' }}
                                                   
                                                </td>
                                            @foreach($activePaymentMethods as $method)
                                                <td class="py-[1px] px-1 text-right text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                    @if($zalazarData[$method] > 0)${{ number_format($zalazarData[$method], 0) }}@else-@endif</td>
                                            @endforeach
                                            <td
                                                class="py-[1px] px-1 text-right text-green-600 dark:text-green-400 print:text-green-700">
                                                +${{ number_format($zalazarData['total_collected'], 0) }}
                                            </td>
                                        </tr>
                                    @endif

                                    @foreach ($zalazarBalancePayments as $movement)
                                        @php
                                            // Obtener payment_details del pago referenciado
                                            $paymentDetailsBreakdown = collect();
                                            if ($movement->reference && $movement->reference instanceof \App\Models\Payment) {
                                                $paymentDetailsBreakdown = $movement->reference->paymentDetails->keyBy('payment_method');
                                            }
                                        @endphp
                                        <tr>
                                            <td class="py-[1px] px-1 text-gray-900 dark:text-white print:text-black">
                                                Pago de Saldos</td>
                                                <td class="py-[1px] px-1 text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                    {{ $movement->description }}</td>
                                            @foreach($activePaymentMethods as $method)
                                                <td class="py-[1px] px-1 text-right text-gray-600 dark:text-gray-400 print:text-gray-700">
                                                    @if($paymentDetailsBreakdown->has($method))
                                                        ${{ number_format($paymentDetailsBreakdown[$method]->amount, 0) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td
                                                class="py-[1px] px-1 text-right text-green-600 dark:text-green-400 print:text-green-700">
                                                +${{ number_format($movement->amount, 0) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="border-t-2 border-gray-300 dark:border-gray-500 print:border-gray-500">
                                        <td class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                            TOTAL:</td>
                                        <td></td>
                                        @foreach($activePaymentMethods as $method)
                                            <td class="py-[1px] px-1 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                                @if($summary['zalazar_payment_breakdown'][$method] > 0)${{ number_format($summary['zalazar_payment_breakdown'][$method], 0) }}@else-@endif</td>
                                        @endforeach
                                        <td
                                            class="py-[1px] px-1 text-right font-bold text-green-600 dark:text-green-400 print:text-green-700">
                                            +${{ number_format($summary['zalazar_total_income'], 0) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pie del Reporte -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-1">
                <div
                    class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 print:text-gray-700 print:text-xs">
                    <p>Total de movimientos: {{ $movements->count() }}</p>
                    <p>Generado por: {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dailyReportForm() {
            return {
                init() {
                    // Auto-print si viene con parámetro print
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('print') === 'true') {
                        setTimeout(() => {
                            window.print();
                            // Cerrar la ventana después de imprimir
                            setTimeout(() => window.close(), 500);
                        }, 500);
                    }
                }
            }
        }
    </script>

    @push('styles')
        <style>
            /* Estilos específicos para el reporte de caja */
            .page-break-inside-avoid {
                page-break-inside: avoid;
            }

            @media print {
                @page {
                    margin: 0.5cm;
                    size: A4;
                }

                body {
                    margin: 0;
                    padding: 0;
                }

                /* Ocultar encabezados y pies de página del navegador */
                @page :first {
                    margin-top: 0;
                }

                @page {
                    margin-top: 0;
                }
            }
        </style>
    @endpush

@endsection
