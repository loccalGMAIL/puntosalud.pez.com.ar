@extends('layouts.app')

@section('title', 'Retención de Pacientes - ' . config('app.name'))
@section('mobileTitle', 'Retención')

@section('content')
<div class="p-6" x-data="retencionForm()">

    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Reportes</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Pacientes – Retención</span>
        </nav>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pacientes – Tasa de Retención</h1>
                <p class="text-gray-600 dark:text-gray-400">Pacientes que vuelven vs visita única en el período</p>
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
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pacientes únicos</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUnique) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">atendidos en el período</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-blue-200 dark:border-blue-700 p-5">
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Volvieron (>1 visita)</p>
            <p class="text-3xl font-bold text-blue-700 dark:text-blue-400">{{ number_format($returning) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">tuvieron más de 1 turno</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-emerald-300 dark:border-emerald-600 p-5">
            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Tasa de Retención</p>
            <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">{{ $retentionRate }}%</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">pacientes que repitieron</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nuevos vs Recurrentes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Nuevos vs Recurrentes</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-32 text-sm text-gray-700 dark:text-gray-300">Nuevos</div>
                    <div class="flex-1 h-6 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        @php $newPct = $totalUnique > 0 ? $newPatients / $totalUnique * 100 : 0; @endphp
                        <div class="h-full bg-emerald-500 rounded-full flex items-center pl-2" style="width: {{ max(5, round($newPct, 1)) }}%">
                            <span class="text-xs text-white font-bold">{{ $newPatients }}</span>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-10 text-right">{{ round($newPct, 1) }}%</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-32 text-sm text-gray-700 dark:text-gray-300">Recurrentes</div>
                    <div class="flex-1 h-6 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        @php $recurPct = $totalUnique > 0 ? $recurringPatients / $totalUnique * 100 : 0; @endphp
                        <div class="h-full bg-blue-500 rounded-full flex items-center pl-2" style="width: {{ max(5, round($recurPct, 1)) }}%">
                            <span class="text-xs text-white font-bold">{{ $recurringPatients }}</span>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-10 text-right">{{ round($recurPct, 1) }}%</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-32 text-sm text-gray-700 dark:text-gray-300">Visita única</div>
                    <div class="flex-1 h-6 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        @php $singlePct = $totalUnique > 0 ? $singleVisit / $totalUnique * 100 : 0; @endphp
                        <div class="h-full bg-gray-400 rounded-full flex items-center pl-2" style="width: {{ max(5, round($singlePct, 1)) }}%">
                            <span class="text-xs text-white font-bold">{{ $singleVisit }}</span>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-10 text-right">{{ round($singlePct, 1) }}%</span>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Nuevos: primera cita ever en el período. Recurrentes: tenían citas anteriores al período.</p>
        </div>

        <!-- Distribución de visitas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Distribución de Visitas por Paciente</h3>
            @if($visitDistribution->count() > 0)
            <div class="space-y-2">
                @foreach($visitDistribution as $visits => $count)
                @php $pct = $totalUnique > 0 ? $count / $totalUnique * 100 : 0; @endphp
                <div class="flex items-center gap-3">
                    <div class="w-20 text-sm text-gray-700 dark:text-gray-300 text-right">{{ $visits }} {{ $visits == 1 ? 'visita' : 'visitas' }}</div>
                    <div class="flex-1 h-5 bg-gray-100 dark:bg-gray-700 rounded overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded" style="width: {{ max(2, round($pct, 1)) }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-12 text-right">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-400 dark:text-gray-500 text-sm">Sin datos</p>
            @endif
        </div>
    </div>

</div>

<script>
function retencionForm() {
    return {
        loading: false,
        filters: {
            date_from: '{{ $dateFrom }}',
            date_to:   '{{ $dateTo }}',
        },
        generateReport() {
            this.loading = true;
            const params = new URLSearchParams(Object.entries(this.filters).filter(([,v]) => v));
            window.location.href = '{{ route('reports.pacientes.retencion') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams(Object.entries(this.filters).filter(([,v]) => v));
            params.set('print', '1');
            window.open('{{ route('reports.pacientes.retencion.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
