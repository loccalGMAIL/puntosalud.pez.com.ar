@extends('layouts.app')

@section('title', 'Comisiones por Profesional - ' . config('app.name'))
@section('mobileTitle', 'Comisiones')

@section('content')
<div class="p-6" x-data="profesionalesComisionesForm()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                <span>Reportes</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                <span>Profesionales – Comisiones</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profesionales – Comisiones</h1>
            <p class="text-gray-600 dark:text-gray-400">Comisiones liquidadas por profesional en el período</p>
        </div>
        <button @click="printReport()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimir
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form @submit.prevent="generateReport()" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Desde</label>
                <input x-model="filters.date_from" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Hasta</label>
                <input x-model="filters.date_to" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profesional</label>
                <select x-model="filters.professional_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Todos</option>
                    @foreach($allProfessionals as $pro)
                        <option value="{{ $pro->id }}" {{ (string)($professionalId ?? '') === (string)$pro->id ? 'selected' : '' }}>{{ $pro->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" :disabled="loading" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors">
                    <span x-show="!loading">Generar</span>
                    <span x-show="loading" class="flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Liquidaciones</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['liquidations_count']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Facturado</p>
            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($totals['total_collected'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Para Profesionales</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($totals['professional_amount'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Para Clínica</p>
            <p class="text-xl font-bold text-purple-600 dark:text-purple-400">${{ number_format($totals['clinic_amount'], 2) }}</p>
        </div>
    </div>

    <!-- Cards por profesional -->
    @forelse($byProfessional as $p)
    <div x-data="{ expanded: false }" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-3">
        <div class="p-4 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
            <div class="flex-1 grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="col-span-2 md:col-span-1">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $p['full_name'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['specialty'] }} · {{ $p['commission_pct'] }}% comisión</p>
                </div>
                <div class="text-right md:text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Liq.</p>
                    <p class="font-medium text-gray-700 dark:text-gray-300">{{ $p['liquidations_count'] }}</p>
                </div>
                <div class="text-right md:text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Facturado</p>
                    <p class="font-medium text-blue-700 dark:text-blue-400">${{ number_format($p['total_collected'], 2) }}</p>
                </div>
                <div class="text-right md:text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Comisión</p>
                    <p class="font-bold text-emerald-700 dark:text-emerald-400">${{ number_format($p['professional_amount'], 2) }}</p>
                </div>
                <div class="text-right md:text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Clínica</p>
                    <p class="font-medium text-purple-700 dark:text-purple-400">${{ number_format($p['clinic_amount'], 2) }}</p>
                </div>
            </div>
            <svg :class="{ 'rotate-90': expanded }" class="w-5 h-5 text-gray-400 ml-4 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </div>

        @if($p['by_month']->count() > 0)
        <div x-show="expanded" x-cloak class="border-t border-gray-100 dark:border-gray-700 px-4 pb-4 pt-2">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Desglose mensual</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-1 px-2">Mes</th>
                            <th class="text-right py-1 px-2">Facturado</th>
                            <th class="text-right py-1 px-2">Comisión</th>
                            <th class="text-right py-1 px-2">Clínica</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($p['by_month'] as $monthKey => $monthData)
                        <tr class="border-b border-gray-50 dark:border-gray-700/50">
                            <td class="py-1 px-2 text-gray-600 dark:text-gray-400 capitalize">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->isoFormat('MMMM YYYY') }}
                            </td>
                            <td class="py-1 px-2 text-right text-blue-700 dark:text-blue-400">${{ number_format($monthData['total_collected'], 2) }}</td>
                            <td class="py-1 px-2 text-right font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($monthData['professional_amount'], 2) }}</td>
                            <td class="py-1 px-2 text-right text-purple-700 dark:text-purple-400">${{ number_format($monthData['clinic_amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400 font-medium">Sin liquidaciones en el período seleccionado</p>
    </div>
    @endforelse

</div>

<script>
function profesionalesComisionesForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ $dateFrom }}',
            date_to:   '{{ $dateTo }}',
            professional_id: '{{ $professionalId ?? '' }}',
        },
        generateReport() {
            this.loading = true;
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            window.location.href = '{{ route('reports.profesionales.comisiones') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            params.set('print', '1');
            window.open('{{ route('reports.profesionales.comisiones.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
