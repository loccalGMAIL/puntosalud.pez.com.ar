@extends('layouts.app')

@section('title', 'Liquidaciones Históricas - ' . config('app.name'))
@section('mobileTitle', 'Liquidaciones Históricas')

@section('content')
<div class="p-6" x-data="liquidacionesHistoricasForm()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                <span>Reportes</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                <span>Liquidaciones Históricas</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Liquidaciones Históricas</h1>
            <p class="text-gray-600 dark:text-gray-400">Historial completo de liquidaciones por período</p>
        </div>
        <button @click="printReport()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
            Imprimir
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros</h2>
        <form @submit.prevent="generateReport()" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Desde</label>
                <input x-model="filters.date_from" type="date"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Hasta</label>
                <input x-model="filters.date_to" type="date"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profesional</label>
                <select x-model="filters.professional_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Todos</option>
                    @foreach($allProfessionals as $pro)
                        <option value="{{ $pro->id }}" {{ (string)($professionalId ?? '') === (string)$pro->id ? 'selected' : '' }}>{{ $pro->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                <select x-model="filters.payment_status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="paid">Pagado</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" :disabled="loading"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors">
                    <span x-show="!loading">Generar</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Cargando...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Liquidaciones</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['count']) }}</p>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pendientes</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $totals['pending_count'] }}</p>
        </div>
    </div>

    <!-- Tabla por Mes -->
    @forelse($byMonth as $month => $monthLiquidations)
    @php
        $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMMM YYYY');
        $monthTotal = $monthLiquidations->sum('total_collected');
        $monthProf  = $monthLiquidations->sum('net_professional_amount');
        $monthClinic= $monthLiquidations->sum('clinic_amount');
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-4">
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 rounded-t-lg flex items-center justify-between border-b border-gray-200 dark:border-gray-600">
            <h3 class="font-semibold text-gray-900 dark:text-white capitalize">{{ $monthLabel }}</h3>
            <div class="flex gap-6 text-sm">
                <span class="text-gray-600 dark:text-gray-400">{{ $monthLiquidations->count() }} liq.</span>
                <span class="font-medium text-blue-600 dark:text-blue-400">${{ number_format($monthTotal, 2) }}</span>
                <span class="font-medium text-emerald-600 dark:text-emerald-400">${{ number_format($monthProf, 2) }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <th class="text-left py-2 px-4 font-medium">Fecha</th>
                        <th class="text-left py-2 px-4 font-medium">Profesional</th>
                        <th class="text-left py-2 px-4 font-medium hidden md:table-cell">Especialidad</th>
                        <th class="text-right py-2 px-4 font-medium">Facturado</th>
                        <th class="text-right py-2 px-4 font-medium hidden md:table-cell">Para Prof.</th>
                        <th class="text-right py-2 px-4 font-medium hidden md:table-cell">Para Clínica</th>
                        <th class="text-center py-2 px-4 font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($monthLiquidations as $liq)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">{{ $liq->liquidation_date->format('d/m/Y') }}</td>
                        <td class="py-2 px-4 text-sm font-medium text-gray-900 dark:text-white">{{ $liq->professional->full_name }}</td>
                        <td class="py-2 px-4 text-sm text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ $liq->professional->specialty?->name ?? '—' }}</td>
                        <td class="py-2 px-4 text-sm text-right font-medium text-blue-700 dark:text-blue-400">${{ number_format($liq->total_collected, 2) }}</td>
                        <td class="py-2 px-4 text-sm text-right text-emerald-700 dark:text-emerald-400 hidden md:table-cell">${{ number_format($liq->net_professional_amount, 2) }}</td>
                        <td class="py-2 px-4 text-sm text-right text-purple-700 dark:text-purple-400 hidden md:table-cell">${{ number_format($liq->clinic_amount, 2) }}</td>
                        <td class="py-2 px-4 text-center">
                            @if($liq->payment_status === 'paid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">Pagado</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.150 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No hay liquidaciones en el período seleccionado</p>
    </div>
    @endforelse

</div>

<script>
function liquidacionesHistoricasForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ $dateFrom }}',
            date_to:   '{{ $dateTo }}',
            professional_id: '{{ $professionalId ?? '' }}',
            payment_status:  '{{ $paymentStatus }}',
        },
        generateReport() {
            this.loading = true;
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            window.location.href = '{{ route('reports.liquidaciones-historicas') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            params.set('print', '1');
            window.open('{{ route('reports.liquidaciones-historicas.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
