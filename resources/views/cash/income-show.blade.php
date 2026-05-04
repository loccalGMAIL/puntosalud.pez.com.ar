@extends('layouts.app')

@section('title', 'Ingreso Manual #' . $payment->receipt_number . ' - ' . config('app.name'))
@section('mobileTitle', 'Detalle Ingreso')

@section('content')
@php
    $methodLabels = [
        'cash' => 'Efectivo',
        'transfer' => 'Transferencia',
        'credit_card' => 'Tarjeta Crédito',
        'debit_card' => 'Tarjeta Débito',
        'card' => 'Tarjeta',
        'qr' => 'Pago QR',
    ];
    $categoryName = $payment->income_category
        ? \App\Models\MovementType::where('code', $payment->income_category)->first()?->name
        : null;
@endphp
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('payments.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Pagos</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Ingreso #{{ $payment->receipt_number }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalle del Ingreso Manual</h1>
            <p class="text-gray-600 dark:text-gray-400">Información completa del ingreso registrado</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('payments.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver
            </a>

            <a href="{{ route('cash.income-receipt', $payment) }}?print=1"
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Imprimir Recibo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Datos del Ingreso -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                        </svg>
                        Información del Ingreso
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Número de Recibo</label>
                            <div class="text-lg font-mono text-gray-900 dark:text-white">{{ $payment->receipt_number }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha y Hora</label>
                            <div class="text-lg text-gray-900 dark:text-white">
                                {{ $payment->payment_date->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Categoría</label>
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                {{ $categoryName ?? 'Ingreso Manual' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Método de Pago</label>
                            <div class="text-lg text-gray-900 dark:text-white">
                                @forelse($payment->paymentDetails as $detail)
                                    <div>{{ $methodLabels[$detail->payment_method] ?? $detail->payment_method }} - ${{ number_format($detail->amount, 2) }}</div>
                                @empty
                                    <div class="text-gray-500 dark:text-gray-400">-</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Monto Recibido</label>
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                ${{ number_format($payment->total_amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Concepto -->
            @if($payment->concept)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Concepto
                        </h2>
                        <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-600 rounded-lg p-4 text-gray-900 dark:text-white">
                            {{ $payment->concept }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Información de Registro -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Registrado por
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Usuario</label>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $payment->createdBy->name ?? 'Sistema' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de creación</label>
                            <div class="text-gray-900 dark:text-white">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumen</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Tipo:</span>
                            <span class="text-gray-900 dark:text-white">Ingreso Manual</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Estado:</span>
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                No aplica liquidación
                            </span>
                        </div>
                        @if($payment->updated_at != $payment->created_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Última modificación:</span>
                                <span class="text-gray-900 dark:text-white">{{ $payment->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
