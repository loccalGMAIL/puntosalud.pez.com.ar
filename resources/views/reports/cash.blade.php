@extends('layouts.app')

@section('title', 'Reporte de Caja - ' . $cashSummary['date']->format('d/m/Y'))
@section('mobileTitle', 'Reporte de Caja')

@section('content')
<div class="p-6" x-data="cashReportForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Reporte de Caja</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reporte de Caja</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $cashSummary['date']->format('d/m/Y') }}</p>
        </div>

        <div class="flex gap-3">
            @if($cashSummary['is_closed'])
                <!-- Botón Reimprimir Reporte -->
                <a href="{{ route('cash.daily-report', ['date' => $cashSummary['date']->format('Y-m-d'), 'print' => 'true']) }}"
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                    </svg>
                    Reimprimir
                </a>
            @endif
            <a href="{{ route('cash.report') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.150 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                Ver Reportes
            </a>
        </div>
    </div>

    <!-- Filtros de Movimientos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <form @submit.prevent="filterByDate()" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                        <input x-model="selectedDate"
                               type="date"
                               @change="filterByDate()"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="flex gap-2">
                        <button type="button"
                                @click="selectedDate = '{{ now()->format('Y-m-d') }}'; filterByDate()"
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                            Hoy
                        </button>
                    </div>
                </form>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                <select x-model="filters.type"
                        @change="applyFilters()"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">Todos</option>
                    <option value="patient_payment">Pagos Pacientes</option>
                    <option value="professional_payment">Pagos Profesionales</option>
                    <option value="expense">Gastos</option>
                    <option value="refund">Reembolsos</option>
                    <option value="other">Otros</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                <select x-model="filters.reference_type"
                        @change="applyFilters()"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">Todas</option>
                    <option value="payment">Pagos</option>
                    <option value="expense">Gastos</option>
                    <option value="refund">Reembolsos</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="clearFilters()"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen de Caja -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Saldo Inicial -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Saldo Inicial</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        ${{ number_format($cashSummary['initial_balance'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Ingresos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Ingresos</p>
                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($cashSummary['total_inflows'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Egresos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Egresos</p>
                    <p class="text-lg font-bold text-red-600 dark:text-red-400">
                        -${{ number_format($cashSummary['total_outflows'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Saldo Final -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Saldo Final</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        ${{ number_format($cashSummary['final_balance'], 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Tipo de Movimiento -->
    @if($movementsByType->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumen por Tipo de Movimiento</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($movementsByType as $type => $data)
                    @if(!in_array($type, ['cash_opening', 'cash_closing']))
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-medium text-gray-900 dark:text-white">
                            {{ $data['icon'] ?? '' }} {{ $data['type_name'] ?? ucfirst(str_replace('_', ' ', $type)) }}
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $data['count'] }} movimientos</span>
                    </div>
                    <div class="space-y-1 text-sm">
                        @if($data['inflows'] > 0)
                        <div class="flex justify-between">
                            <span class="text-green-600 dark:text-green-400">Ingresos:</span>
                            <span class="text-green-600 dark:text-green-400 font-semibold text-lg">+${{ number_format($data['inflows'], 2) }}</span>
                        </div>
                        @endif
                        @if($data['outflows'] > 0)
                        <div class="flex justify-between">
                            <span class="text-red-600 dark:text-red-400">Egresos:</span>
                            <span class="text-red-600 dark:text-red-400 font-semibold text-lg">-${{ number_format($data['outflows'], 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Movimientos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Movimientos del Día ({{ $movements->count() }})
            </h2>

            @if($movements->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-600">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">ID</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Hora</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Tipo</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Usuario</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Concepto</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Monto</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Saldo</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($movements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 text-sm font-mono text-gray-500 dark:text-gray-400">
                                #{{ $movement->id }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $movement->created_at->format('H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $typeColor = match($movement->movementType?->color) {
                                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                        'teal' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $typeColor }}">
                                    {{ $movement->movementType?->icon }} {{ $movement->movementType?->name ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-xs font-medium">
                                        {{ strtoupper(substr($movement->user->name ?? 'SYS', 0, 2)) }}
                                    </div>
                                    <span>{{ $movement->user->name ?? 'Sistema' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                @if($movement->movementType?->code === 'professional_payment' && $movement->reference_type === 'App\\Models\\Professional' && $movement->reference_id)
                                    @php
                                        $professional = \App\Models\Professional::find($movement->reference_id);
                                    @endphp
                                    @if($professional)
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->description }}</div>
                                            <span class="font-medium">Dr. {{ $professional->first_name }} {{ $professional->last_name }}</span>
                                        </div>
                                    @else
                                        {{ $movement->description }}
                                    @endif
                                @else
                                    {{ $movement->description }}
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-sm font-medium">
                                @if($movement->amount > 0)
                                <span class="text-green-600 dark:text-green-400">
                                    +${{ number_format($movement->amount, 2) }}
                                </span>
                                @else
                                <span class="text-red-600 dark:text-red-400">
                                    ${{ number_format($movement->amount, 2) }}
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-sm font-mono text-gray-900 dark:text-white">
                                ${{ number_format($movement->balance_after, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewMovementDetails({{ $movement->id }})"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Ver detalles">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    @if($movement->movementType?->code === 'patient_payment' && $movement->reference_id)
                                        <a href="{{ route('payments.print-receipt', $movement->reference_id) }}?print=1"
                                           target="_blank"
                                           class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                           title="Imprimir recibo">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No hay movimientos registrados para este día</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal para ver detalles del movimiento -->
    <div x-show="movementDetailsModalVisible"
         x-cloak
         class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
         @click.self="closeMovementDetailsModal()">
        <div class="modal-content bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Detalles del Movimiento
                    </h2>
                    <button @click="closeMovementDetailsModal()"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div x-show="movementDetailsLoading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500 mx-auto"></div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Cargando detalles...</p>
                </div>

                <div x-show="!movementDetailsLoading && movementDetails" class="space-y-4">
                    <!-- Información básica del movimiento -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Información del Movimiento</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">ID:</span>
                                <span class="font-mono text-gray-900 dark:text-white ml-2">#<span x-text="movementDetails?.id"></span></span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Fecha:</span>
                                <span class="text-gray-900 dark:text-white ml-2" x-text="formatDate(movementDetails?.created_at)"></span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Tipo:</span>
                                <span class="text-gray-900 dark:text-white ml-2" x-text="movementDetails?.movement_type?.icon + ' ' + movementDetails?.movement_type?.name"></span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Usuario:</span>
                                <span class="text-gray-900 dark:text-white ml-2" x-text="movementDetails?.user?.name || 'Sistema'"></span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Monto:</span>
                                <span class="font-medium ml-2"
                                      :class="movementDetails?.amount > 0 ? 'text-green-600' : 'text-red-600'"
                                      x-text="formatAmount(movementDetails?.amount)"></span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Saldo resultante:</span>
                                <span class="font-mono text-gray-900 dark:text-white ml-2" x-text="formatAmount(movementDetails?.balance_after)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles específicos para pagos de pacientes -->
                    <div x-show="movementDetails?.movement_type?.code === 'patient_payment' && paymentDetails"
                         class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h3 class="font-medium text-blue-900 dark:text-blue-200 mb-3">💰 Detalles del Pago</h3>
                        <div class="text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-blue-700 dark:text-blue-300">Número de recibo:</span>
                                <span class="font-mono text-blue-900 dark:text-blue-100" x-text="paymentDetails?.receipt_number"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700 dark:text-blue-300">Paciente:</span>
                                <span class="text-blue-900 dark:text-blue-100 font-medium" x-text="paymentDetails?.patient?.full_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700 dark:text-blue-300">Método de pago:</span>
                                <span class="text-blue-900 dark:text-blue-100" x-text="getPaymentMethodLabel(paymentDetails?.payment_method)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700 dark:text-blue-300">Tipo de pago:</span>
                                <span class="text-blue-900 dark:text-blue-100" x-text="paymentDetails?.is_package ? 'Paquete de tratamiento' : 'Pago individual'"></span>
                            </div>
                            <!-- Mostrar profesionales relacionados -->
                            <div x-show="paymentDetails?.payment_appointments?.length > 0" class="pt-2 border-t border-blue-200 dark:border-blue-700">
                                <span class="text-blue-700 dark:text-blue-300">Profesional:</span>
                                <div class="mt-1 space-y-1">
                                    <template x-for="appointment in paymentDetails?.payment_appointments" :key="appointment.id">
                                        <div class="text-blue-900 dark:text-blue-100">
                                            • Dr. <span x-text="appointment?.appointment?.professional?.first_name + ' ' + appointment?.appointment?.professional?.last_name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div x-show="paymentDetails?.notes" class="pt-2 border-t border-blue-200 dark:border-blue-700">
                                <span class="text-blue-700 dark:text-blue-300">Notas:</span>
                                <p class="text-blue-900 dark:text-blue-100 mt-1" x-text="paymentDetails?.notes"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles específicos para liquidaciones de profesionales -->
                    <div x-show="movementDetails?.movement_type?.code === 'professional_payment' && professionalDetails"
                         class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <h3 class="font-medium text-green-900 dark:text-green-200 mb-3">👨‍⚕️ Detalles de la Liquidación</h3>
                        <div class="text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-green-300">Profesional:</span>
                                <span class="text-green-900 dark:text-green-100 font-medium">
                                    Dr. <span x-text="professionalDetails?.first_name + ' ' + professionalDetails?.last_name"></span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-green-300">Especialidad:</span>
                                <span class="text-green-900 dark:text-green-100" x-text="professionalDetails?.specialty?.name"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles específicos para reintegros a pacientes -->
                    <div x-show="movementDetails?.movement_type?.code === 'expense' && refundProfessionalDetails"
                         class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                        <h3 class="font-medium text-yellow-900 dark:text-yellow-200 mb-3">🔄 Detalles del Reintegro</h3>
                        <div class="text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-yellow-700 dark:text-yellow-300">Profesional Responsable:</span>
                                <span class="text-yellow-900 dark:text-yellow-100 font-medium">
                                    Dr. <span x-text="refundProfessionalDetails?.first_name + ' ' + refundProfessionalDetails?.last_name"></span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-yellow-700 dark:text-yellow-300">Especialidad:</span>
                                <span class="text-yellow-900 dark:text-yellow-100" x-text="refundProfessionalDetails?.specialty?.name"></span>
                            </div>
                            <div class="pt-2 border-t border-yellow-200 dark:border-yellow-700">
                                <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                    💡 Este monto será descontado de la liquidación del profesional
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción del movimiento -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-2">Descripción</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300" x-text="movementDetails?.description"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cashReportForm() {
    return {
        selectedDate: '{{ $cashSummary["date"]->format("Y-m-d") }}',
        filters: {
            type: '',
            reference_type: ''
        },

        // Modal de detalles de movimiento
        movementDetailsModalVisible: false,
        movementDetailsLoading: false,
        movementDetails: null,
        paymentDetails: null,
        professionalDetails: null,
        refundProfessionalDetails: null,

        filterByDate() {
            window.location.href = `/reports/cash?date=${this.selectedDate}`;
        },

        applyFilters() {
            const params = new URLSearchParams();
            params.set('date', this.selectedDate);

            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });

            window.location.href = `/reports/cash?${params.toString()}`;
        },

        clearFilters() {
            this.filters = {
                type: '',
                reference_type: ''
            };
            this.filterByDate();
        },

        async viewMovementDetails(movementId) {
            this.movementDetailsLoading = true;
            this.movementDetailsModalVisible = true;
            this.movementDetails = null;
            this.paymentDetails = null;
            this.professionalDetails = null;
            this.refundProfessionalDetails = null;

            try {
                const response = await fetch(`/cash/movements/${movementId}`);
                const data = await response.json();

                if (data.success) {
                    this.movementDetails = data.cash_movement;

                    // Usar los datos adicionales del endpoint mejorado
                    if (data.additional_data) {
                        this.paymentDetails = data.additional_data.payment || null;
                        this.professionalDetails = data.additional_data.professional || null;
                        this.refundProfessionalDetails = data.additional_data.refund_professional || null;
                    }
                } else {
                    alert('Error al cargar los detalles del movimiento');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar los detalles del movimiento');
            } finally {
                this.movementDetailsLoading = false;
            }
        },

        closeMovementDetailsModal() {
            this.movementDetailsModalVisible = false;
            this.movementDetails = null;
            this.paymentDetails = null;
            this.professionalDetails = null;
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString('es-AR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatAmount(amount) {
            if (amount === null || amount === undefined) return '';
            const prefix = amount > 0 ? '+$' : '$';
            return prefix + Math.abs(amount).toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        getPaymentMethodLabel(method) {
            const methods = {
                'cash': 'Efectivo',
                'transfer': 'Transferencia',
                'card': 'Tarjeta'
            };
            return methods[method] || method;
        }
    }
}
</script>

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* Asegurar que el modal esté por encima de todo */
.modal-overlay {
    z-index: 10000 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
}

.modal-content {
    position: relative !important;
    z-index: 10001 !important;
}
</style>
@endpush

@endsection
