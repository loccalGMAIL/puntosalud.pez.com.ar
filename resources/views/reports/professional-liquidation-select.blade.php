@extends('layouts.app')

@section('title', 'Seleccionar Liquidación Profesional - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            💰 Liquidación de Profesionales
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Generar reporte de liquidación para entregar al profesional al final del día
        </p>
    </div>

    <!-- Selector de Fecha -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-center">
            <div class="w-full max-w-sm">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center">
                    📅 Fecha de Liquidación
                </label>
                <input type="date" 
                       name="date" 
                       id="dateSelector"
                       value="{{ $selectedDate->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white text-center">
            </div>
        </div>
    </div>

    <!-- Profesionales con Liquidación Pendiente -->
    @if($professionalsWithLiquidation->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                💼 Profesionales con Liquidación Pendiente
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                    ({{ $professionalsWithLiquidation->count() }} {{ $professionalsWithLiquidation->count() === 1 ? 'profesional' : 'profesionales' }} atendieron pacientes hoy)
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($professionalsWithLiquidation as $professional)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white">
                                    Dr. {{ $professional['full_name'] }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $professional['specialty']->name }}
                                </p>
                                <div class="mt-3 space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Pacientes atendidos:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $professional['attended_count'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Total generado:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">${{ number_format($professional['total_amount'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm border-t border-gray-200 dark:border-gray-600 pt-1">
                                        <span class="text-emerald-700 dark:text-emerald-400 font-medium">A liquidar:</span>
                                        <span class="font-bold text-emerald-700 dark:text-emerald-400">${{ number_format($professional['professional_amount'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1 ml-3">
                                <a href="{{ route('reports.professional-liquidation', ['professional_id' => $professional['id'], 'date' => $selectedDate->format('Y-m-d')]) }}"
                                   class="inline-flex items-center px-3 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-medium rounded transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Ver
                                </a>
                                <a href="{{ route('reports.professional-liquidation', ['professional_id' => $professional['id'], 'date' => $selectedDate->format('Y-m-d'), 'print' => 1]) }}"
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded transition-colors print-link">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                    Imprimir
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-8">
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No hay liquidaciones pendientes</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    No se encontraron profesionales que hayan atendido pacientes el {{ $selectedDate->format('d/m/Y') }}.
                    <br>
                    Puedes seleccionar otro día para generar liquidaciones.
                </p>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('#dateSelector');
    
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('date', this.value);
            window.location.href = currentUrl.toString();
        });
    }

    // Handle print links - auto close after printing
    document.querySelectorAll('.print-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const printWindow = window.open(this.href, '_blank');
            
            if (printWindow) {
                printWindow.addEventListener('load', function() {
                    setTimeout(function() {
                        printWindow.addEventListener('afterprint', function() {
                            printWindow.close();
                        });
                    }, 1000);
                });
            }
        });
    });
});
</script>
@endsection