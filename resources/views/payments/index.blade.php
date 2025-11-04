@extends('layouts.app')

@section('title', 'Ingresos - ' . config('app.name'))
@section('mobileTitle', 'Ingresos')

@section('content')
<div class="p-6" x-data="paymentsPage()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Pagos</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Ingresos</h1>
            <p class="text-gray-600 dark:text-gray-400">Pagos de pacientes e ingresos manuales</p>
        </div>
        
        <div class="flex gap-3">
            {{-- Botón "Crear pago" removido: Los pagos se crean desde turnos --}}
            {{-- <a href="{{ route('payments.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Pago
            </a> --}}
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5 mb-6">
        <!-- Total Pagos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pagos</dt>
                    <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</dd>
                </div>
            </div>
        </div>

        <!-- Efectivo -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Efectivo</dt>
                    <dd class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_cash'] }}</dd>
                </div>
            </div>
        </div>

        <!-- Transferencias -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Transferencias</dt>
                    <dd class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_transfers'] }}</dd>
                </div>
            </div>
        </div>

        <!-- Pendientes de Liquidación -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes</dt>
                    <dd class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_liquidation'] }}</dd>
                </div>
            </div>
        </div>

        <!-- Liquidados -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Liquidados</dt>
                    <dd class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['liquidated'] }}</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v5.721c0 .926-.492 1.784-1.285 2.246l-.686.343a1.125 1.125 0 01-1.462-.396l-.423-.618a1.125 1.125 0 01-.194-.682v-5.938a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Filtros y Búsqueda
                </h3>
                <button @click="clearFilters()" 
                        class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 font-medium">
                    Limpiar filtros
                </button>
            </div>
            
            <form @submit.prevent="applyFilters()" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Búsqueda -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <input x-model="filters.search" 
                           type="text" 
                           placeholder="Paciente, DNI o N° recibo..." 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Tipo de Pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select x-model="filters.payment_type" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="single">Individual</option>
                        <option value="package">Paquete</option>
                        <option value="refund">Reembolso</option>
                    </select>
                </div>

                <!-- Método de Pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método</label>
                    <select x-model="filters.payment_method" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="cash">💵 Efectivo</option>
                        <option value="transfer">🏦 Transferencia</option>
                        <option value="debit_card">💳 Tarjeta de Débito</option>
                        <option value="credit_card">💳 Tarjeta de Crédito</option>
                    </select>
                </div>

                <!-- Estado Liquidación -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select x-model="filters.liquidation_status" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="pending">A liquidar</option>
                        <option value="liquidated">Liquidado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>

                <!-- Botón Filtrar -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-6">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 17.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                Todos los Ingresos
            </h3>
            
            <div class="rounded-md border border-gray-200 dark:border-gray-600 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Recibo</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Paciente / De</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                {{-- Columna "Tipo" removida para ahorrar espacio --}}
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Método</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-24">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 {{ $payment->entry_type === 'income' ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}">
                                    <!-- Recibo -->
                                    <td class="px-3 py-2">
                                        <div class="text-sm font-mono text-gray-900 dark:text-white">{{ $payment->receipt_number }}</div>
                                    </td>

                                    <!-- Paciente / De -->
                                    <td class="px-3 py-2">
                                        @if($payment->entry_type === 'payment')
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $payment->patient->full_name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">DNI: {{ $payment->patient->dni }}</div>
                                        @else
                                            {{-- Ingreso Manual --}}
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                @if($payment->reference_type === 'App\\Models\\Professional' && $payment->reference)
                                                    Dr. {{ $payment->reference->full_name }}
                                                @else
                                                    Ingreso Manual
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($payment->description, 40) }}</div>
                                        @endif
                                    </td>

                                    <!-- Fecha -->
                                    <td class="px-3 py-2">
                                        @php
                                            $displayDate = $payment->entry_type === 'payment' ? $payment->payment_date : $payment->created_at;
                                        @endphp
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $displayDate->format('d/m/Y') }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $displayDate->format('H:i') }}</div>
                                    </td>
                                    
                                    {{-- Columna "Tipo" eliminada (info resumida en Paciente/De o en Método) --}}

                                    <!-- Método -->
                                    <td class="px-3 py-2">
                                        @if($payment->entry_type === 'payment')
                                            @php
                                                $methodLabels = [
                                                    'cash' => 'Efectivo',
                                                    'transfer' => 'Transferencia',
                                                    'debit_card' => 'Débito',
                                                    'credit_card' => 'Crédito',
                                                    'card' => 'Tarjeta'
                                                ];
                                            @endphp
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}</span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Monto -->
                                    <td class="px-3 py-2">
                                        @if($payment->entry_type === 'payment')
                                            <div class="text-sm font-semibold {{ $payment->payment_type === 'refund' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ $payment->payment_type === 'refund' ? '-' : '' }}${{ number_format($payment->amount, 2) }}
                                            </div>
                                        @else
                                            <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                                ${{ number_format($payment->amount, 2) }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-3 py-2">
                                        @if($payment->entry_type === 'payment')
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                    'liquidated' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                    'not_applicable' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'Para liquidar',
                                                    'liquidated' => 'Liquidado',
                                                    'cancelled' => 'Cancelado',
                                                    'not_applicable' => 'No aplica'
                                                ];

                                                // Manejo especial para refunds (anulaciones)
                                                $currentStatus = $payment->liquidation_status;
                                                $statusColor = $statusColors[$currentStatus] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
                                                $statusLabel = $statusLabels[$currentStatus] ?? ucfirst($currentStatus);

                                                // Si es un refund, mostrar "No aplica" independiente del estado
                                                if ($payment->payment_type === 'refund') {
                                                    $statusColor = 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
                                                    $statusLabel = 'No aplica';
                                                }
                                            @endphp
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                                {{ $statusLabel }}
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                                N/A
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($payment->entry_type === 'payment')
                                                <!-- Ver Pago de Paciente -->
                                                <a href="{{ route('payments.show', $payment) }}"
                                                   class="p-1 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                                                   title="Ver detalle">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </a>

                                                <!-- Anular Pago -->
                                                @if($payment->liquidation_status === 'pending' && $payment->payment_type !== 'refund' && !str_contains($payment->concept ?? '', '[ANULADO'))
                                                    <button onclick="annulPayment({{ $payment->id }}, '{{ $payment->receipt_number }}')"
                                                            class="p-1 text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded transition-colors"
                                                            title="Anular Pago">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            @else
                                                <!-- Imprimir Recibo de Ingreso Manual -->
                                                <a href="{{ route('cash.income-receipt', $payment->id) }}?print=1"
                                                   target="_blank"
                                                   class="p-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors"
                                                   title="Imprimir Recibo">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                                            </svg>
                                            <p class="text-gray-600 dark:text-gray-400">No se encontraron pagos</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-500">Los pagos se registran desde la creación de turnos</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            @if($payments->hasPages())
                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function paymentsPage() {
    return {
        filters: {
            search: '{{ request('search') }}',
            payment_type: '{{ request('payment_type') }}',
            payment_method: '{{ request('payment_method') }}',
            liquidation_status: '{{ request('liquidation_status') }}',
            date_from: '{{ request('date_from') }}',
            date_to: '{{ request('date_to') }}'
        },

        applyFilters() {
            const url = new URL(window.location.href);
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    url.searchParams.set(key, this.filters[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            
            window.location.href = url.toString();
        },

        clearFilters() {
            this.filters = {
                search: '',
                payment_type: '',
                payment_method: '',
                liquidation_status: '',
                date_from: '',
                date_to: ''
            };
            this.applyFilters();
        }
    }
}

async function annulPayment(paymentId, receiptNumber) {
    const confirmed = confirm(
        `¿Está seguro de que desea ANULAR el pago con recibo #${receiptNumber}?\n\n` +
        `Esta acción:\n` +
        `• Creará un movimiento de caja negativo\n` +
        `• Liberará el/los turno(s) asociado(s) para nuevo cobro\n` +
        `• Generará un nuevo recibo de anulación\n\n` +
        `Esta operación NO se puede revertir.`
    );

    if (!confirmed) return;

    try {
        const response = await fetch(`/payments/${paymentId}/annul`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            alert(`✓ Pago anulado exitosamente.\n\nRecibo de anulación: ${result.refund_receipt}\n\n${result.message}`);
            window.location.reload();
        } else {
            alert(`✗ Error: ${result.message}`);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('✗ Error al procesar la anulación. Por favor, intente nuevamente.');
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection