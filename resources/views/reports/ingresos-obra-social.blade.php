@extends('layouts.app')

@section('title', 'Ingresos por Obra Social - ' . config('app.name'))
@section('mobileTitle', 'Obra Social')

@section('content')
<div class="p-6" x-data="obraSocialForm()">

    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Reportes</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Ingresos por Obra Social</span>
        </nav>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ingresos por Obra Social</h1>
                <p class="text-gray-600 dark:text-gray-400">Facturación y turnos agrupados por financiador</p>
            </div>
            <button @click="printReport()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form @submit.prevent="generateReport()" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Desde</label>
                <input x-model="filters.date_from" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Hasta</label>
                <input x-model="filters.date_to" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" :disabled="loading" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors">
                    <span x-show="!loading">Generar</span>
                    <span x-show="loading" class="flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Facturado</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($totals['amount'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Turnos</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($totals['count']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Financiadores</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $byInsurance->count() }}</p>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        @if($byInsurance->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <th class="text-left py-3 px-4 font-medium">#</th>
                        <th class="text-left py-3 px-4 font-medium">Obra Social / Financiador</th>
                        <th class="text-center py-3 px-4 font-medium">Turnos</th>
                        <th class="text-right py-3 px-4 font-medium">Facturado</th>
                        <th class="text-right py-3 px-4 font-medium hidden md:table-cell">Promedio</th>
                        <th class="text-left py-3 px-4 font-medium hidden lg:table-cell" style="min-width:150px">% del Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($byInsurance as $i => $ins)
                    @php $pct = $totals['amount'] > 0 ? ($ins['amount'] / $totals['amount'] * 100) : 0; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-3 px-4 text-sm text-gray-400 dark:text-gray-500">{{ $i + 1 }}</td>
                        <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">{{ $ins['name'] }}</td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700 dark:text-gray-300">{{ $ins['count'] }}</td>
                        <td class="py-3 px-4 text-sm text-right font-bold text-blue-700 dark:text-blue-400">${{ number_format($ins['amount'], 2) }}</td>
                        <td class="py-3 px-4 text-sm text-right text-gray-600 dark:text-gray-400 hidden md:table-cell">${{ number_format($ins['avg'], 2) }}</td>
                        <td class="py-3 px-4 hidden lg:table-cell">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ round($pct, 1) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 w-10 text-right">{{ round($pct, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-gray-500 bg-gray-50 dark:bg-gray-700">
                        <td class="py-3 px-4"></td>
                        <td class="py-3 px-4 text-sm font-bold text-gray-900 dark:text-white">TOTAL</td>
                        <td class="py-3 px-4 text-sm text-center font-bold text-gray-900 dark:text-white">{{ $totals['count'] }}</td>
                        <td class="py-3 px-4 text-sm text-right font-bold text-blue-700 dark:text-blue-400">${{ number_format($totals['amount'], 2) }}</td>
                        <td class="hidden md:table-cell"></td>
                        <td class="hidden lg:table-cell"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="p-12 text-center"><p class="text-gray-500 dark:text-gray-400 font-medium">Sin datos en el período seleccionado</p></div>
        @endif
    </div>

    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Los importes corresponden al campo "monto final" de turnos atendidos. Pacientes sin obra social registrada aparecen como "Sin obra social".</p>
</div>

<script>
function obraSocialForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ $dateFrom }}',
            date_to:   '{{ $dateTo }}',
        },
        generateReport() {
            this.loading = true;
            const params = new URLSearchParams(Object.entries(this.filters).filter(([,v]) => v));
            window.location.href = '{{ route('reports.ingresos-obra-social') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams(Object.entries(this.filters).filter(([,v]) => v));
            params.set('print', '1');
            window.open('{{ route('reports.ingresos-obra-social.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
