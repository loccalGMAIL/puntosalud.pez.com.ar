@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                💰 Liquidación del Profesional
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Dr. {{ $liquidationData['professional']->full_name }} - {{ $liquidationData['date']->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('reports.professional-liquidation') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Volver
            </a>
            <a href="{{ route('reports.professional-liquidation', ['professional_id' => $liquidationData['professional']->id, 'date' => $liquidationData['date']->format('Y-m-d'), 'print' => 1]) }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Imprimir
            </a>
        </div>
    </div>

    <!-- Professional Info -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Profesional</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Dr. {{ $liquidationData['professional']->full_name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $liquidationData['professional']->specialty->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $liquidationData['date']->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $liquidationData['date']->translatedFormat('l') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Comisión</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $liquidationData['totals']['commission_percentage'] }}%</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $liquidationData['appointments']->count() }} pacientes atendidos</p>
            </div>
        </div>
    </div>

    <!-- Liquidation Summary -->
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-4">
            💰 Resumen de Liquidación
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-emerald-700 dark:text-emerald-300">Total Facturado:</span>
                <span class="font-medium text-emerald-900 dark:text-emerald-100">${{ number_format($liquidationData['totals']['total_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-emerald-700 dark:text-emerald-300">Comisión Clínica ({{ 100 - $liquidationData['totals']['commission_percentage'] }}%):</span>
                <span class="font-medium text-emerald-900 dark:text-emerald-100">-${{ number_format($liquidationData['totals']['clinic_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="border-t border-emerald-200 dark:border-emerald-800 pt-3">
                <div class="flex justify-between">
                    <span class="font-semibold text-emerald-900 dark:text-emerald-100">MONTO A ENTREGAR AL PROFESIONAL:</span>
                    <span class="text-xl font-bold text-emerald-700 dark:text-emerald-400">${{ number_format($liquidationData['totals']['professional_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Turnos Pagados Previamente -->
    @if($liquidationData['prepaid_appointments']->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    💳 Turnos Pagados Previamente ({{ $liquidationData['prepaid_appointments']->count() }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total: ${{ number_format($liquidationData['totals']['prepaid_amount'], 0, ',', '.') }} | 
                    Su comisión: ${{ number_format($liquidationData['totals']['prepaid_professional'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['prepaid_appointments'] as $appointment)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 px-3 font-medium text-gray-900 dark:text-white">{{ $appointment['time'] }}</td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $appointment['patient_dni'] }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ match($appointment['payment_method']) {
                                                'cash' => 'Efectivo',
                                                'transfer' => 'Transferencia', 
                                                'card' => 'Tarjeta',
                                                default => $appointment['payment_method']
                                            } }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $appointment['payment_date'] }}</div>
                                        @if($appointment['receipt_number'])
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Rec: {{ $appointment['receipt_number'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Turnos Pagados Hoy -->
    @if($liquidationData['today_paid_appointments']->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    💰 Turnos Cobrados Hoy ({{ $liquidationData['today_paid_appointments']->count() }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total: ${{ number_format($liquidationData['totals']['today_paid_amount'], 0, ',', '.') }} | 
                    Su comisión: ${{ number_format($liquidationData['totals']['today_paid_professional'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Método</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['today_paid_appointments'] as $appointment)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 px-3 font-medium text-gray-900 dark:text-white">{{ $appointment['time'] }}</td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $appointment['patient_dni'] }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ match($appointment['payment_method']) {
                                                'cash' => 'Efectivo',
                                                'transfer' => 'Transferencia', 
                                                'card' => 'Tarjeta',
                                                default => $appointment['payment_method']
                                            } }}
                                        </div>
                                        @if($appointment['receipt_number'])
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Rec: {{ $appointment['receipt_number'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Turnos Sin Pagar -->
    @if($liquidationData['unpaid_appointments']->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="bg-red-50 dark:bg-red-900/20 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    ⚠️ Turnos Pendientes de Pago ({{ $liquidationData['unpaid_appointments']->count() }})
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total pendiente: ${{ number_format($liquidationData['totals']['unpaid_amount'], 0, ',', '.') }} | 
                    Su comisión pendiente: ${{ number_format($liquidationData['totals']['unpaid_professional'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-right py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Monto</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidationData['unpaid_appointments'] as $appointment)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 px-3 font-medium text-gray-900 dark:text-white">{{ $appointment['time'] }}</td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $appointment['patient_dni'] }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                            PENDIENTE
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
        Liquidación generada el {{ $liquidationData['generated_at']->format('d/m/Y H:i:s') }} por {{ $liquidationData['generated_by'] }}
    </div>
</div>
@endsection