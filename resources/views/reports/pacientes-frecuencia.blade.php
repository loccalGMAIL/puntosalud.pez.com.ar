@extends('layouts.app')

@section('title', 'Frecuencia de Visitas - ' . config('app.name'))
@section('mobileTitle', 'Frecuencia Visitas')

@section('content')
<div class="p-6" x-data="frecuenciaForm()">

    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Reportes</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Pacientes – Frecuencia</span>
        </nav>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pacientes – Frecuencia de Visitas</h1>
                <p class="text-gray-600 dark:text-gray-400">Promedio de días entre visitas consecutivas</p>
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
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Promedio Global</p>
            @if($globalAvg !== null)
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $globalAvg }} días</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">entre visitas consecutivas</p>
            @else
                <p class="text-xl font-medium text-gray-400">Sin datos</p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Con múltiples visitas</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($patientsWithMultiple) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">pacientes analizados</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pacientes</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPatients) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">en el período</p>
        </div>
    </div>

    <!-- Distribución por buckets -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Distribución por Intervalo entre Visitas</h2>
        @if($patientsWithMultiple > 0)
        <div class="space-y-4">
            @foreach($buckets as $label => $count)
            @php
                $pct = $patientsWithMultiple > 0 ? $count / $patientsWithMultiple * 100 : 0;
                $colors = [
                    '1-7 días'   => 'bg-emerald-500',
                    '8-14 días'  => 'bg-blue-500',
                    '15-30 días' => 'bg-amber-500',
                    '31-60 días' => 'bg-orange-500',
                    '> 60 días'  => 'bg-red-500',
                ];
                $color = $colors[$label] ?? 'bg-gray-500';
            @endphp
            <div class="flex items-center gap-4">
                <div class="w-24 text-sm font-medium text-gray-700 dark:text-gray-300 text-right flex-shrink-0">{{ $label }}</div>
                <div class="flex-1 h-7 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                    <div class="{{ $color }} h-full rounded-lg flex items-center px-3 transition-all" style="width: {{ max($count > 0 ? 3 : 0, round($pct, 1)) }}%">
                        @if($count > 0)
                        <span class="text-xs text-white font-bold">{{ $count }}</span>
                        @endif
                    </div>
                </div>
                <div class="w-24 text-sm text-right">
                    <span class="font-bold text-gray-900 dark:text-white">{{ $count }}</span>
                    <span class="text-gray-400 dark:text-gray-500"> ({{ round($pct, 1) }}%)</span>
                </div>
            </div>
            @endforeach
        </div>
        <p class="mt-5 text-xs text-gray-400 dark:text-gray-500">Solo incluye pacientes con 2 o más visitas en el período. El intervalo es el promedio de días entre citas consecutivas por paciente.</p>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">No hay pacientes con múltiples visitas en el período seleccionado</p>
        </div>
        @endif
    </div>

</div>

<script>
function frecuenciaForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ $dateFrom }}',
            date_to:   '{{ $dateTo }}',
        },
        generateReport() {
            this.loading = true;
            const params = new URLSearchParams(Object.entries(this.filters).filter(([,v]) => v));
            window.location.href = '{{ route('reports.pacientes.frecuencia') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams(Object.entries(this.filters).filter(([,v]) => v));
            params.set('print', '1');
            window.open('{{ route('reports.pacientes.frecuencia.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
