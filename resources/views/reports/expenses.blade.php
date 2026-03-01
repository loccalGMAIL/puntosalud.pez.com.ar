@extends('layouts.app')

@section('title', 'Informe de Gastos - ' . config('app.name'))
@section('mobileTitle', 'Informe de Gastos')

@section('content')
<div class="p-6" x-data="expensesReportForm()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Reportes</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Informe de Gastos</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Informe de Gastos</h1>
            <p class="text-gray-600 dark:text-gray-400">Análisis detallado de gastos operativos por período</p>
        </div>

        <div class="flex gap-3">
            <button @click="exportReport('excel')"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Excel
            </button>
            <button @click="exportReport('pdf')"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </button>
            <button @click="exportReport('print')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- Filtros del Reporte -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros del Reporte</h2>
        <form @submit.prevent="generateReport()" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Desde</label>
                <input x-model="filters.date_from"
                       type="date"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Hasta</label>
                <input x-model="filters.date_to"
                       type="date"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Gasto</label>
                <select x-model="filters.movement_type_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Todos los tipos</option>
                    @foreach($expenseTypes as $type)
                        <option value="{{ $type->id }}" {{ (string)$movementTypeId === (string)$type->id ? 'selected' : '' }}>
                            {{ $type->icon }} {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        :disabled="loading"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-lg transition-colors disabled:cursor-not-allowed">
                    <span x-show="!loading">Generar</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Cargando...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Resumen del Período -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <!-- Total Gastos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Gastos</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        -${{ number_format($totalAmount, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Cantidad de Registros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.150 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Registros</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ number_format($totalCount) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mayor tipo de gasto -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                    </svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Mayor tipo de gasto</p>
                    @if($topType)
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $topType['icon'] }} {{ $topType['name'] }}
                        </p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">
                            -${{ number_format($topType['total'], 2) }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sin datos</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por Tipo de Gasto -->
    @if($byType->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Análisis por Tipo de Gasto</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($byType as $item)
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium text-gray-900 dark:text-white">
                        {{ $item['icon'] }} {{ $item['name'] }}
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item['count'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-red-600 dark:text-red-400">Egresos:</span>
                    <span class="text-red-600 dark:text-red-400 font-medium">-${{ number_format($item['total'], 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detalle de Gastos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Detalle de Gastos ({{ $totalCount }} registros)
            </h2>

            @if($movements->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-600">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Fecha</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Hora</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Tipo</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Descripción</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Monto</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($movements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $movement->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $movement->created_at->format('H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $typeColor = match($movement->movementType?->color) {
                                        'green'  => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'blue'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'red'    => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                        'teal'   => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
                                        default  => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $typeColor }}">
                                    {{ $movement->movementType?->icon }} {{ $movement->movementType?->name ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                {{ $movement->description ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-right font-medium text-red-600 dark:text-red-400">
                                -${{ number_format(abs($movement->amount), 2) }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $movement->user?->name ?? 'Sistema' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No hay gastos registrados en el período seleccionado</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Probá cambiando el rango de fechas o el tipo de gasto</p>
            </div>
            @endif
        </div>
    </div>

</div>

<script>
function expensesReportForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ $dateFrom }}',
            date_to:   '{{ $dateTo }}',
            movement_type_id: '{{ $movementTypeId ?? '' }}',
        },

        generateReport() {
            this.loading = true;
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });
            window.location.href = '/reports/expenses?' + params.toString();
        },

        exportReport(format) {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });
            if (format === 'excel') {
                window.location.href = '/reports/expenses/export?' + params.toString();
            } else if (format === 'pdf') {
                window.location.href = '/reports/expenses/pdf?' + params.toString();
            } else if (format === 'print') {
                params.set('print', '1');
                window.open('/reports/expenses/print?' + params.toString(), '_blank');
            }
        },
    }
}
</script>

@endsection
