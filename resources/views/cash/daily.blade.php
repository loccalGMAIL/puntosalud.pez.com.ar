@extends('layouts.app')

@section('title', 'Caja del Día - ' . $cashSummary['date']->format('d/m/Y'))
@section('mobileTitle', 'Caja del Día')

@section('content')
<div class="p-6" x-data="dailyCashForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Caja del Día</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Caja del Día</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $cashSummary['date']->format('d/m/Y') }}</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('cash.expense-form') }}" 
               class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Registrar Gasto
            </a>
            <a href="{{ route('cash.report') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.150 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                Ver Reportes
            </a>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form @submit.prevent="filterByDate()" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                <input x-model="selectedDate" 
                       type="date" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Filtrar
                </button>
                <button type="button" 
                        @click="selectedDate = '{{ now()->format('Y-m-d') }}'; filterByDate()"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Hoy
                </button>
            </div>
        </form>
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
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Inicial</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
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
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ingresos</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        +${{ number_format($cashSummary['total_inflows'], 2) }}
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
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Egresos</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
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
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Final</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
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
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-medium text-gray-900 dark:text-white">
                            @switch($type)
                                @case('patient_payment')
                                    💰 Pagos Pacientes
                                    @break
                                @case('professional_payment')
                                    👨‍⚕️ Pagos Profesionales
                                    @break
                                @case('expense')
                                    💸 Gastos
                                    @break
                                @case('refund')
                                    🔄 Reembolsos
                                    @break
                                @case('cash_opening')
                                    🔓 Apertura de Caja
                                    @break
                                @case('cash_closing')
                                    🔒 Cierre de Caja
                                    @break
                                @case('cash_control')
                                    🔍 Control de Caja
                                    @break
                                @case('shift_handover')
                                    🔄 Entrega de Turno
                                    @break
                                @case('shift_receive')
                                    📥 Recibo de Turno
                                    @break
                                @case('other')
                                    📋 Otros
                                    @break
                                @default
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                            @endswitch
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $data['count'] }} movimientos</span>
                    </div>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-green-600">Ingresos:</span>
                            <span class="text-green-600">+${{ number_format($data['inflows'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-600">Egresos:</span>
                            <span class="text-red-600">-${{ number_format($data['outflows'], 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-1">
                            <span class="font-medium text-gray-900 dark:text-white">Neto:</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                ${{ number_format($data['inflows'] - $data['outflows'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros de Movimientos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

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
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Hora</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Tipo</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Concepto</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Referencia</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Monto</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Saldo</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($movements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $movement->created_at->format('H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                @switch($movement->type)
                                    @case('patient_payment')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            💰 Pago Paciente
                                        </span>
                                        @break
                                    @case('professional_payment')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            👨‍⚕️ Pago Profesional
                                        </span>
                                        @break
                                    @case('expense')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            💸 Gasto
                                        </span>
                                        @break
                                    @case('refund')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            🔄 Reembolso
                                        </span>
                                        @break
                                    @case('other')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            📋 Otro
                                        </span>
                                        @break
                                    @case('cash_opening')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                            🔓 Apertura de Caja
                                        </span>
                                        @break
                                    @case('cash_closing')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                            🔒 Cierre de Caja
                                        </span>
                                        @break
                                    @case('cash_control')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            🔍 Control de Caja
                                        </span>
                                        @break
                                    @case('shift_handover')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                            🔄 Entrega de Turno
                                        </span>
                                        @break
                                    @case('shift_receive')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                            📥 Recibo de Turno
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            {{ ucfirst($movement->type) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                {{ $movement->description }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $movement->reference_type ?? 'N/A')) }}
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
                                <button @click="viewMovementDetails({{ $movement->id }})" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
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
</div>

<script>
function dailyCashForm() {
    return {
        selectedDate: '{{ $cashSummary["date"]->format("Y-m-d") }}',
        filters: {
            type: '',
            reference_type: ''
        },

        filterByDate() {
            window.location.href = `/cash/daily?date=${this.selectedDate}`;
        },

        applyFilters() {
            const params = new URLSearchParams();
            params.set('date', this.selectedDate);
            
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });
            
            window.location.href = `/cash/daily?${params.toString()}`;
        },

        clearFilters() {
            this.filters = {
                type: '',
                reference_type: ''
            };
            this.filterByDate();
        },

        async viewMovementDetails(movementId) {
            // Implementar modal de detalles
            console.log('Ver detalles del movimiento:', movementId);
        }
    }
}
</script>

@endsection