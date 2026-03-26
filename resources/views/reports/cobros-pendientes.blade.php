@extends('layouts.app')

@section('title', 'Cobros Pendientes - ' . config('app.name'))
@section('mobileTitle', 'Cobros Pendientes')

@section('content')
<div class="p-6" x-data="cobrosPendientesForm()">

    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Reportes</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Cobros Pendientes</span>
        </nav>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cobros Pendientes</h1>
                <p class="text-gray-600 dark:text-gray-400">Turnos atendidos sin pago registrado</p>
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
    <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-700 p-5">
            <p class="text-sm font-medium text-red-600 dark:text-red-400">Turnos sin cobrar</p>
            <p class="text-3xl font-bold text-red-700 dark:text-red-400">{{ number_format($totals['count']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-orange-200 dark:border-orange-700 p-5">
            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Estimado pendiente</p>
            <p class="text-3xl font-bold text-orange-700 dark:text-orange-400">${{ number_format($totals['estimated_total'], 2) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">basado en montos estimados</p>
        </div>
    </div>

    @if($pending->count() > 0)
    <!-- Por profesional -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-5">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Por Profesional</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($byProfessional as $bpItem)
            @php $firstApp = $pending->firstWhere('professional_id', $pending->first()->professional_id); @endphp
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-100 dark:border-red-800">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $pending->firstWhere(fn($a) => $a->professional?->id && $bpItem['count'] > 0)?->professional?->full_name ?? '—' }}</p>
                <div class="flex justify-between mt-1 text-sm">
                    <span class="text-red-600 dark:text-red-400">{{ $bpItem['count'] }} turnos</span>
                    <span class="font-bold text-red-700 dark:text-red-400">${{ number_format($bpItem['amount'], 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tabla detalle -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <th class="text-left py-3 px-4 font-medium">Fecha</th>
                        <th class="text-left py-3 px-4 font-medium">Paciente</th>
                        <th class="text-left py-3 px-4 font-medium">Teléfono</th>
                        <th class="text-left py-3 px-4 font-medium">Profesional</th>
                        <th class="text-right py-3 px-4 font-medium">Estimado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($pending as $app)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">{{ $app->appointment_date->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-4 text-sm font-medium text-gray-900 dark:text-white">{{ $app->patient?->full_name ?? '—' }}</td>
                        <td class="py-2 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $app->patient?->phone ?? '—' }}</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">{{ $app->professional?->full_name ?? '—' }}</td>
                        <td class="py-2 px-4 text-sm text-right font-medium text-orange-700 dark:text-orange-400">
                            {{ $app->estimated_amount ? '$' . number_format($app->estimated_amount, 2) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Mobile -->
        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($pending as $app)
            <div class="p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $app->patient?->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $app->appointment_date->format('d/m/Y H:i') }} · {{ $app->professional?->full_name ?? '—' }}</p>
                    </div>
                    <span class="text-sm font-bold text-orange-700 dark:text-orange-400">{{ $app->estimated_amount ? '$' . number_format($app->estimated_amount, 2) : '—' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-emerald-200 dark:border-emerald-700 p-12 text-center">
        <svg class="w-12 h-12 text-emerald-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-emerald-600 dark:text-emerald-400 font-medium">Sin cobros pendientes en el período seleccionado</p>
    </div>
    @endif

</div>

<script>
function cobrosPendientesForm() {
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
            window.location.href = '{{ route('reports.cobros-pendientes') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            params.set('print', '1');
            window.open('{{ route('reports.cobros-pendientes.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
