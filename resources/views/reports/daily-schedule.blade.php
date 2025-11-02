@extends('layouts.app')

@section('title', 'Pacientes a Atender - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                📋 Listado de Pacientes del Día
            </h1>
            {{-- <p class="text-gray-600 dark:text-gray-400">
                Dr. {{ $reportData['professional']->full_name }} - {{ $reportData['date']->format('d/m/Y') }}
            </p> --}}
        </div>
        <div class="flex gap-3">
            <a href="{{ route('reports.daily-schedule') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Volver
            </a>
            <button onclick="window.print()"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- Professional Info -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Profesional</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Dr. {{ $reportData['professional']->full_name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reportData['professional']->specialty->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $reportData['date']->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reportData['date']->translatedFormat('l') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Pacientes</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $reportData['stats']['total_appointments'] }} turnos</p>
                {{-- <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reportData['stats']['scheduled'] }} programados</p> --}}
            </div>
        </div>
    </div>

    <!-- Tabla de Pacientes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                📋 Pacientes del Día ({{ $reportData['appointments']->count() }})
            </h3>
            {{-- <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $reportData['stats']['scheduled'] }} programados
            </p> --}}
        </div>

        @if($reportData['appointments']->count() > 0)
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Hora</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Paciente</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Estado</th>
                                <th class="text-left py-2 px-3 text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($reportData['appointments'] as $appointment)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-3 px-3 font-medium text-gray-900 dark:text-white">
                                    {{ $appointment['time'] }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        DNI: {{ $appointment['patient_dni'] }}
                                        @if($appointment['patient_insurance'])
                                            | {{ $appointment['patient_insurance'] }}
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        @if($appointment['status'] === 'scheduled') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                        @elseif($appointment['status'] === 'attended') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                        @elseif($appointment['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                        @elseif($appointment['status'] === 'absent') bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                                        @endif">
                                        {{ $appointment['status_label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $appointment['notes'] ?: '-' }}</div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay pacientes para atender</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    El profesional no tiene pacientes asignados para {{ $reportData['date']->format('d/m/Y') }}
                </p>
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    @page {
        margin: 1cm;
        size: A4;
    }

    /* Ocultar sidebar y elementos de navegación */
    [x-data]:first-of-type > div:first-child,
    .fixed.left-0.top-0,
    .fixed.inset-0.z-40,
    nav,
    .no-print,
    button,
    .bg-gray-600,
    header,
    aside,
    .lg\:hidden {
        display: none !important;
    }

    /* Resetear el margin-left del contenido principal */
    [class*="lg:ml-"] {
        margin-left: 0 !important;
    }

    /* Ajustar el container para impresión ULTRA-COMPACTO */
    .container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Resetear colores de fondo para impresión */
    body {
        background: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        font-size: 12px !important;
        line-height: 1.3 !important;
    }

    /* Asegurar que los badges y colores se vean bien */
    .bg-emerald-50,
    .bg-emerald-100,
    .bg-yellow-50,
    .bg-yellow-100,
    .bg-green-50,
    .bg-green-100,
    .bg-red-50,
    .bg-red-100,
    .bg-gray-100 {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Ocultar botones del header */
    .mb-6.flex.items-center.justify-between > div:last-child {
        display: none !important;
    }

    /* Tamaños de fuente más grandes para mejor legibilidad */
    h1 {
        font-size: 21px !important;
        margin-bottom: 5px !important;
    }

    h2, h3 {
        font-size: 14px !important;
        margin-bottom: 4px !important;
    }

    p, div, span {
        font-size: 12px !important;
    }

    /* Reducir padding y márgenes extremadamente */
    .px-4, .px-6 {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }

    .py-4, .py-6 {
        padding-top: 3px !important;
        padding-bottom: 3px !important;
    }

    .p-6 {
        padding: 4px !important;
    }

    .mb-6 {
        margin-bottom: 6px !important;
    }

    .mb-4 {
        margin-bottom: 4px !important;
    }

    .gap-6 {
        gap: 4px !important;
    }

    /* Tablas compactas con mejor legibilidad */
    table {
        page-break-inside: avoid;
        font-size: 12px !important;
    }

    thead th {
        padding: 3px 4px !important;
        font-size: 11px !important;
    }

    tbody td {
        padding: 3px 4px !important;
        font-size: 12px !important;
    }

    tfoot td {
        padding: 3px 4px !important;
        font-size: 12px !important;
    }

    .text-xs {
        font-size: 10px !important;
    }

    .text-sm {
        font-size: 11px !important;
    }

    .text-base, .text-lg {
        font-size: 12px !important;
    }

    .text-xl {
        font-size: 14px !important;
    }

    .text-2xl {
        font-size: 15px !important;
    }

    tr {
        page-break-inside: avoid;
    }

    /* Reducir espacio en cards y secciones */
    .rounded-lg {
        margin-bottom: 4px !important;
    }

    /* Ajustar badges y tags */
    .inline-flex.items-center {
        padding: 2px 4px !important;
        font-size: 10px !important;
    }

    /* Eliminar sombras y bordes gruesos en print */
    .shadow-sm {
        box-shadow: none !important;
    }

    /* Primera card: Professional Info en dos columnas horizontales */
    .grid.grid-cols-1.md\:grid-cols-3 {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        justify-content: space-between !important;
        gap: 8px !important;
    }

    .grid.grid-cols-1.md\:grid-cols-3 > div {
        flex: 0 0 48% !important;
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
        align-items: center !important;
    }

    .grid.grid-cols-1.md\:grid-cols-3 > div h3 {
        font-weight: bold !important;
        min-width: 80px !important;
        margin-bottom: 0 !important;
    }

    .grid.grid-cols-1.md\:grid-cols-3 > div p {
        margin: 0 !important;
    }
}
</style>

<script>
// Auto-imprimir si viene desde el selector de reportes
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('autoPrint') === 'true') {
        sessionStorage.removeItem('autoPrint');
        // Pequeño delay para que la página cargue completamente
        setTimeout(function() {
            window.print();
        }, 500);
    }
});
</script>

@endsection