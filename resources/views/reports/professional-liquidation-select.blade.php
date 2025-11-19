@extends('layouts.app')

@section('title', 'Seleccionar Liquidación Profesional - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3 h-3 mr-2.5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Reportes</span>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Liquidación Profesionales</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                💰 Liquidación de Profesionales
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Generar reporte de liquidación para entregar al profesional al final del día
            </p>
        </div>
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            Volver al Dashboard
        </a>
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
                                    @if($professional['refunds'] > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-red-600 dark:text-red-400">Reintegros:</span>
                                        <span class="font-medium text-red-700 dark:text-red-400">-${{ number_format($professional['refunds'], 0, ',', '.') }}</span>
                                    </div>
                                    @endif
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
                                <button class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded transition-colors" onclick="navigateAndPrint('{{ route('reports.professional-liquidation', ['professional_id' => $professional['id'], 'date' => $selectedDate->format('Y-m-d')]) }}')"
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                    Imprimir
                                </button>
                                {{-- Mostrar botón siempre que haya turnos atendidos (incluso con monto $0) --}}
                                @if($professional['attended_count'] > 0)
                                <button onclick="liquidarProfesional({{ $professional['id'] }}, '{{ $professional['full_name'] }}', {{ $professional['professional_amount'] }}, '{{ $selectedDate->format('Y-m-d') }}')"
                                        class="inline-flex items-center px-3 py-1 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs font-medium rounded transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m-1.125 3.75c0-.621.504-1.125 1.125-1.125h1.5v1.5h-1.5A1.125 1.125 0 013.375 8.25zM6 21V3.75h.75A1.875 1.875 0 018.625 2.25H12m0 0h3.375c1.035 0 1.875.84 1.875 1.875v16.5h-6" />
                                    </svg>
                                    Liquidar
                                </button>
                                @endif
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
// Función para navegar al reporte y abrir el diálogo de impresión
function navigateAndPrint(url) {
    // Guardamos en sessionStorage que queremos imprimir
    sessionStorage.setItem('autoPrint', 'true');
    // Navegamos a la página del reporte
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('#dateSelector');

    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('date', this.value);
            window.location.href = currentUrl.toString();
        });
    }
});

// Función para liquidar profesional
async function liquidarProfesional(professionalId, professionalName, amount, date) {
    // Mostrar modal de confirmación
    const confirmed = await SystemModal.confirm(
        'Confirmar Liquidación',
        `¿Confirmar liquidación de <strong>Dr. ${professionalName}</strong> por <strong>$${amount.toLocaleString()}</strong>?<br><br>Esto registrará el pago en caja y descontará el monto del efectivo disponible.`,
        'Liquidar',
        'Cancelar'
    );

    if (!confirmed) return;

    try {
        const response = await fetch('/liquidation/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                professional_id: professionalId,
                amount: amount,
                date: date
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Error en la operación');
        }

        // Mostrar mensaje de éxito
        await SystemModal.show(
            'success',
            'Liquidación Procesada',
            `Dr. ${professionalName}\nMonto: $${amount.toLocaleString()}\nNuevo saldo en caja: $${result.data.new_balance.toLocaleString()}`,
            'Aceptar'
        );

        // Recargar la página para actualizar los datos
        window.location.reload();

    } catch (error) {
        // Mostrar modal de error
        await SystemModal.show(
            'error',
            'Error al Procesar Liquidación',
            error.message,
            'Aceptar'
        );
        console.error('Error:', error);
    }
}
</script>
@endsection