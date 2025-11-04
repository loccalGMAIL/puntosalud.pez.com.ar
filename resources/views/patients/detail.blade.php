@extends('layouts.app')

@section('title', 'Paciente: ' . $patient->full_name . ' - ' . config('app.name'))
@section('mobileTitle', 'Detalle Paciente')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('patients.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Pacientes</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>{{ $patient->full_name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalle del Paciente</h1>
            <p class="text-gray-600 dark:text-gray-400">Información completa del paciente y su historial</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('patients.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Datos del Paciente -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Información Personal
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre Completo</label>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $patient->last_name }}, {{ $patient->first_name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">DNI</label>
                            <div class="text-lg font-mono text-gray-900 dark:text-white">{{ $patient->dni }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Nacimiento</label>
                            <div class="text-lg text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Edad</label>
                            <div class="text-lg text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($patient->birth_date)->age }} años
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Teléfono</label>
                            <div class="text-lg text-gray-900 dark:text-white">{{ $patient->phone ?: 'No especificado' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Email</label>
                            <div class="text-lg text-gray-900 dark:text-white">{{ $patient->email ?: 'No especificado' }}</div>
                        </div>

                        @if($patient->address)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Dirección</label>
                                <div class="text-lg text-gray-900 dark:text-white">{{ $patient->address }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información de Obra Social -->
            @if($patient->health_insurance)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Información de Obra Social
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Obra Social</label>
                                <div class="text-lg text-gray-900 dark:text-white">{{ $patient->health_insurance }}</div>
                            </div>

                            @if($patient->health_insurance_number)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Número de Afiliado</label>
                                    <div class="text-lg font-mono text-gray-900 dark:text-white">{{ $patient->health_insurance_number }}</div>
                                </div>
                            @endif

                            @if($patient->titular_obra_social)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Titular</label>
                                    <div class="text-lg text-gray-900 dark:text-white">{{ $patient->titular_obra_social }}</div>
                                </div>
                            @endif

                            @if($patient->plan_obra_social)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Plan</label>
                                    <div class="text-lg text-gray-900 dark:text-white">{{ $patient->plan_obra_social }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Estado del Paciente -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estado</h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Estado Actual</label>
                            @if($patient->activo)
                                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumen</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Fecha de registro:</span>
                            <span class="text-gray-900 dark:text-white">{{ $patient->created_at->format('d/m/Y') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Total de turnos:</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ $appointments->count() }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Turnos atendidos:</span>
                            <span class="text-gray-900 dark:text-white">{{ $appointments->where('status', 'attended')->count() }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Turnos programados:</span>
                            <span class="text-gray-900 dark:text-white">{{ $appointments->where('status', 'scheduled')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Turnos - Ancho Completo -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                </svg>
                Historial de Turnos ({{ $appointments->count() }})
            </h2>

            @if($appointments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha y Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Profesional</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($appointments as $appointment)
                                @php
                                    $firstPaymentAppointment = $appointment->paymentAppointments->first();
                                    $paymentReceipt = $firstPaymentAppointment?->payment?->receipt_number ?? null;

                                    $statusColors = [
                                        'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                        'attended' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'absent' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                    ];
                                    $statusLabels = [
                                        'scheduled' => 'Programado',
                                        'attended' => 'Atendido',
                                        'absent' => 'Ausente',
                                        'cancelled' => 'Cancelado'
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400">#{{ $appointment->id }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}hs
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            Dr. {{ $appointment->professional->first_name }} {{ $appointment->professional->last_name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$appointment->status] }}">
                                            {{ $statusLabels[$appointment->status] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            @if($appointment->final_amount)
                                                ${{ number_format($appointment->final_amount, 2) }}
                                            @elseif($appointment->estimated_amount)
                                                ${{ number_format($appointment->estimated_amount, 2) }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Sin especificar</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($paymentReceipt)
                                            <span class="inline-flex px-2 py-1 text-xs font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 rounded">
                                                {{ $paymentReceipt }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">No hay turnos registrados para este paciente</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection
