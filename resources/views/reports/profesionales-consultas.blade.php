@extends('layouts.app')

@section('title', 'Consultas por Profesional - ' . config('app.name'))
@section('mobileTitle', 'Consultas por Prof.')

@section('content')
<div class="p-6" x-data="profesionalesConsultasForm()">

    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Reportes</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            <span>Profesionales – Consultas</span>
        </nav>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profesionales – Consultas</h1>
                <p class="text-gray-600 dark:text-gray-400">Desglose de turnos por estado para cada profesional</p>
            </div>
            @if($professionals->count() > 0)
            <button @click="printReport()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                Imprimir
            </button>
            @endif
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form @submit.prevent="generateReport()" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" :disabled="loading"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors">
                    <span x-show="!loading">Generar</span>
                    <span x-show="loading" class="flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Turnos</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($globalTotal) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-emerald-200 dark:border-emerald-700 p-4">
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Atendidos</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($globalAttended) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-yellow-200 dark:border-yellow-700 p-4">
            <p class="text-xs font-medium text-yellow-600 dark:text-yellow-400">Ausentes</p>
            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ number_format($globalAbsent) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-700 p-4">
            <p class="text-xs font-medium text-red-500 dark:text-red-400">Cancelados</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($globalCancelled) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-emerald-300 dark:border-emerald-600 p-4">
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Tasa Asistencia</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $globalRate }}%</p>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        @if($professionals->count() > 0)
        <!-- Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <th class="text-left py-3 px-4 font-medium">Profesional</th>
                        <th class="text-left py-3 px-4 font-medium">Especialidad</th>
                        <th class="text-center py-3 px-4 font-medium text-emerald-600">Atendidos</th>
                        <th class="text-center py-3 px-4 font-medium text-yellow-600">Ausentes</th>
                        <th class="text-center py-3 px-4 font-medium text-red-500">Cancelados</th>
                        <th class="text-center py-3 px-4 font-medium text-blue-500">Pendientes</th>
                        <th class="text-center py-3 px-4 font-medium">Total</th>
                        <th class="text-center py-3 px-4 font-medium">Asistencia</th>
                        <th class="text-left py-3 px-4 font-medium" style="min-width:120px">Distribución</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($professionals as $p)
                    @php
                        $completedPct   = $p['total'] > 0 ? ($p['attended'] + $p['absent']) / $p['total'] * 100 : 0;
                        $attendedPct    = $p['total'] > 0 ? $p['attended']  / $p['total'] * 100 : 0;
                        $absentPct      = $p['total'] > 0 ? $p['absent']    / $p['total'] * 100 : 0;
                        $cancelledPct   = $p['total'] > 0 ? $p['cancelled'] / $p['total'] * 100 : 0;
                        $scheduledPct   = $p['total'] > 0 ? $p['scheduled'] / $p['total'] * 100 : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">{{ $p['full_name'] }}</td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $p['specialty'] }}</td>
                        <td class="py-3 px-4 text-sm text-center font-medium text-emerald-700 dark:text-emerald-400">{{ $p['attended'] }}</td>
                        <td class="py-3 px-4 text-sm text-center font-medium text-yellow-700 dark:text-yellow-400">{{ $p['absent'] }}</td>
                        <td class="py-3 px-4 text-sm text-center font-medium text-red-600 dark:text-red-400">{{ $p['cancelled'] }}</td>
                        <td class="py-3 px-4 text-sm text-center text-blue-600 dark:text-blue-400">{{ $p['scheduled'] }}</td>
                        <td class="py-3 px-4 text-sm text-center font-bold text-gray-900 dark:text-white">{{ $p['total'] }}</td>
                        <td class="py-3 px-4 text-sm text-center">
                            <span class="font-bold {{ $p['attendance_rate'] >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($p['attendance_rate'] >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $p['attendance_rate'] }}%
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500" style="width: {{ round($attendedPct, 1) }}%" title="Atendidos"></div>
                                <div class="bg-yellow-400" style="width: {{ round($absentPct, 1) }}%" title="Ausentes"></div>
                                <div class="bg-red-400" style="width: {{ round($cancelledPct, 1) }}%" title="Cancelados"></div>
                                <div class="bg-blue-300" style="width: {{ round($scheduledPct, 1) }}%" title="Pendientes"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Mobile -->
        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($professionals as $p)
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $p['full_name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['specialty'] }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $p['attendance_rate'] >= 80 ? 'text-emerald-600' : ($p['attendance_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $p['attendance_rate'] }}%</span>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div><p class="text-emerald-600 font-bold">{{ $p['attended'] }}</p><p class="text-gray-400">Atendidos</p></div>
                    <div><p class="text-yellow-600 font-bold">{{ $p['absent'] }}</p><p class="text-gray-400">Ausentes</p></div>
                    <div><p class="text-red-500 font-bold">{{ $p['cancelled'] }}</p><p class="text-gray-400">Cancel.</p></div>
                    <div><p class="text-gray-700 dark:text-gray-300 font-bold">{{ $p['total'] }}</p><p class="text-gray-400">Total</p></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400 font-medium">Sin datos en el período seleccionado</p>
        </div>
        @endif
    </div>

    <!-- Leyenda -->
    <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-emerald-500"></div> Atendidos</div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-yellow-400"></div> Ausentes</div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-red-400"></div> Cancelados</div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-blue-300"></div> Pendientes</div>
        <span class="ml-2">· Tasa de asistencia calculada sobre turnos completados (atendidos + ausentes)</span>
    </div>
</div>

<script>
function profesionalesConsultasForm() {
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
            window.location.href = '{{ route('reports.profesionales.consultas') }}?' + params.toString();
        },
        printReport() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            params.set('print', '1');
            window.open('{{ route('reports.profesionales.consultas.print') }}?' + params.toString(), '_blank');
        },
    }
}
</script>
@endsection
