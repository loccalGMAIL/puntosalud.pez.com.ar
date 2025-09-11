@extends('layouts.app')

@section('title', 'Pacientes a Atender - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                📋 Listado de Pacientes - Dr. {{ $reportData['professional']->full_name }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $reportData['professional']->specialty->name }} - {{ $reportData['date']->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('reports.daily-schedule', ['professional_id' => $reportData['professional']->id, 'date' => $reportData['date']->format('Y-m-d'), 'print' => 1]) }}"
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Imprimir
            </a>
            <a href="{{ route('reports.daily-schedule') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['stats']['total_appointments'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Pacientes</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $reportData['stats']['scheduled'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Programados</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $reportData['stats']['paid_appointments'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Pagados</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($reportData['stats']['total_estimated'], 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Estimado</div>
            </div>
        </div>
    </div>

    <!-- Tabla de Pacientes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Pacientes del Día - {{ $reportData['date']->translatedFormat('l, d \d\e F') }}
            </h3>
        </div>

        @if($reportData['appointments']->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($reportData['appointments'] as $appointment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $appointment['time'] }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $appointment['patient_name'] }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        DNI: {{ $appointment['patient_dni'] }}
                                        @if($appointment['patient_insurance'])
                                            <br>{{ $appointment['patient_insurance'] }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $appointment['patient_phone'] ?: 'No registrado' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $appointment['office'] }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($appointment['final_amount'])
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</div>
                                    @elseif($appointment['estimated_amount'])
                                        <div class="text-sm text-gray-500 dark:text-gray-400">${{ number_format($appointment['estimated_amount'], 0, ',', '.') }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                    
                                    @if($appointment['is_paid'])
                                        <div class="text-xs text-green-600 dark:text-green-400 font-medium">✓ Pagado</div>
                                        @if($appointment['payment_method'])
                                            <div class="text-xs text-gray-500">
                                                {{ match($appointment['payment_method']) {
                                                    'cash' => 'Efectivo',
                                                    'transfer' => 'Transferencia', 
                                                    'card' => 'Tarjeta',
                                                    default => $appointment['payment_method']
                                                } }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($appointment['status'] === 'scheduled') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($appointment['status'] === 'attended') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($appointment['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($appointment['status'] === 'absent') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                        @endif">
                                        {{ $appointment['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $appointment['notes'] ?: '-' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
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
@endsection