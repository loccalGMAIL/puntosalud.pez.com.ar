@extends('layouts.app')

@section('title', 'Reportes de Caja - ' . config('app.name'))
@section('mobileTitle', 'Reportes de Caja')

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
                <a href="{{ route('cash.daily') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Caja</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Reportes</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reportes de Caja</h1>
            <p class="text-gray-600 dark:text-gray-400">Análisis detallado de movimientos de caja</p>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agrupar Por</label>
                <select x-model="filters.group_by" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" 
                        :disabled="loading"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-lg transition-colors disabled:cursor-not-allowed">
                    <span x-show="!loading">Generar</span>
                    <span x-show="loading" class="flex items-center gap-2">
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Ingresos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Ingresos</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($summary['total_inflows'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Egresos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Egresos</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        ${{ number_format($summary['total_outflows'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Resultado Neto -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Resultado Neto</p>
                    <p class="text-2xl font-bold {{ ($summary['net_amount'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                       ${{ number_format($summary['net_amount'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Movimientos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.150 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Movimientos</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ number_format($summary['movements_count'] ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        en {{ $summary['period_days'] ?? 0 }} días
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por Tipo de Movimiento -->
    @if(isset($movementsByType) && $movementsByType->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Análisis por Tipo de Movimiento</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($movementsByType as $type => $data)
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium text-gray-900 dark:text-white">
                        {{ $data['icon'] }} {{ $data['type_name'] }}
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $data['count'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    @if($data['inflows'] > 0)
                    <span class="text-green-600">Ingresos:</span>
                    <span class="text-green-600 font-medium">+${{ number_format($data['inflows'], 2) }}</span>
                    @else
                    <span class="text-red-600">Egresos:</span>
                    <span class="text-red-600 font-medium">-${{ number_format($data['outflows'], 2) }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    <!-- Datos Tabulares del Período -->
    @if(isset($reportData) && $reportData->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Detalle del Período ({{ $reportData->count() }} períodos)
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-600">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Período</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Ingresos</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Egresos</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Neto</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Movimientos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($reportData as $period)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white font-medium">
                                {{ $period['period_label'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-sm font-medium text-green-600 dark:text-green-400">
                                +${{ number_format($period['inflows'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-sm font-medium text-red-600 dark:text-red-400">
                                -${{ number_format($period['outflows'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-sm font-medium {{ $period['net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $period['net'] >= 0 ? '+' : '' }}${{ number_format($period['net'], 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-sm text-gray-600 dark:text-gray-300">
                                {{ $period['count'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function cashReportForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ request("date_from", now()->startOfMonth()->format("Y-m-d")) }}',
            date_to: '{{ request("date_to", now()->format("Y-m-d")) }}',
            group_by: '{{ request("group_by", "day") }}'
        },

        async generateReport() {
            this.loading = true;
            
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });
            
            window.location.href = `/cash/report?${params.toString()}`;
        },

        async exportReport(format) {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });

            if (format === 'excel') {
                window.location.href = `/cash/report/export?${params.toString()}`;
            } else if (format === 'pdf') {
                window.open(`/cash/report/print?${params.toString()}`, '_blank');
            }
        }
    }
}
</script>

@endsection