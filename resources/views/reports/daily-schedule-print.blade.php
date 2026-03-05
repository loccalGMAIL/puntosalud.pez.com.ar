@extends('layouts.print')

@section('title', 'Listado Diario - ' . $reportData['professional']->full_name . ' - ' . $reportData['date']->format('d/m/Y'))
@section('back-url', route('reports.daily-schedule'))

@section('content')
<div class="p-6 print:p-1">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 print:shadow-none print:border-none">

        <!-- Encabezado del Reporte -->
        <div class="p-2 border-b border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-0.5">
            <x-report-print-header
                title="Listado de Pacientes a Atender"
                :subtitle="$reportData['date']->translatedFormat('l, d \d\e F \d\e Y')"
            />
        </div>

        <div class="p-6 print:p-2">

            <!-- Info del Profesional -->
            <div class="flex gap-4 mb-6 print:mb-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 print:border-gray-400 rounded-lg p-3 print:p-1.5">
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 print:text-gray-600 uppercase">Profesional</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white print:text-black">Dr. {{ $reportData['professional']->full_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 print:text-gray-600">{{ $reportData['professional']->specialty->name }}</p>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 print:text-gray-600 uppercase">Fecha</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white print:text-black">{{ $reportData['date']->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 print:text-gray-600">{{ $reportData['date']->translatedFormat('l') }}</p>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 print:text-gray-600 uppercase">Generado</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white print:text-black">{{ $reportData['generated_at']->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 print:text-gray-600">Por: {{ $reportData['generated_by'] }}</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6 print:gap-1 print:mb-2">
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-gray-700 dark:text-gray-200 print:text-gray-800">Total Pacientes</span>
                        <span class="text-base font-bold text-gray-900 dark:text-white print:text-black print:text-sm">{{ $reportData['stats']['total_appointments'] }}</span>
                    </p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg print:bg-gray-100 print:border print:border-gray-300 print:p-1">
                    <p class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-medium text-blue-900 dark:text-blue-200 print:text-gray-800">Programados</span>
                        <span class="text-base font-bold text-blue-700 dark:text-blue-300 print:text-black print:text-sm">{{ $reportData['stats']['scheduled'] }}</span>
                    </p>
                </div>
            </div>

            <!-- Tabla de Turnos -->
            @if($reportData['appointments']->count() > 0)
            <div class="mb-2 print:mb-0.5">
                <h5 class="report-section-title">Turnos del Día ({{ $reportData['appointments']->count() }})</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] print:text-[9px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-600 print:border-gray-400">
                                <th class="text-center py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:8%">Hora</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:35%">Paciente</th>
                                <th class="text-center py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:12%">Estado</th>
                                <th class="text-left py-[2px] px-1 font-semibold text-gray-900 dark:text-white print:text-black" style="width:45%">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400">
                            @foreach($reportData['appointments'] as $appointment)
                            <tr>
                                <td class="py-[1px] px-1 text-center font-bold text-gray-900 dark:text-white print:text-black">{{ $appointment['time'] }}</td>
                                <td class="py-[1px] px-1">
                                    <span class="font-semibold text-gray-900 dark:text-white print:text-black">{{ $appointment['patient_name'] }}</span>
                                    <br>
                                    <span class="text-[9px] text-gray-500 dark:text-gray-400 print:text-gray-600">
                                        DNI: {{ $appointment['patient_dni'] }}
                                        @if($appointment['patient_insurance'])
                                            | {{ $appointment['patient_insurance'] }}
                                        @endif
                                    </span>
                                </td>
                                <td class="py-[1px] px-1 text-center">
                                    @php
                                        $statusClasses = [
                                            'scheduled' => 'bg-yellow-100 text-yellow-800 print:bg-yellow-50 print:text-yellow-900',
                                            'attended'  => 'bg-green-100 text-green-800 print:bg-green-50 print:text-green-900',
                                            'cancelled' => 'bg-red-100 text-red-800 print:bg-red-50 print:text-red-900',
                                            'absent'    => 'bg-gray-100 text-gray-700 print:bg-gray-50 print:text-gray-800',
                                        ];
                                        $cls = $statusClasses[$appointment['status']] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-block px-1 py-0.5 rounded text-[8px] font-bold uppercase {{ $cls }}">
                                        {{ $appointment['status_label'] }}
                                    </span>
                                </td>
                                <td class="py-[1px] px-1 text-[9px] text-gray-700 dark:text-gray-300 print:text-gray-700">
                                    {{ $appointment['notes'] ?: '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <p class="text-sm text-center text-gray-500 dark:text-gray-400 print:text-gray-600 italic py-6 print:py-2">
                No hay pacientes para atender este día.
            </p>
            @endif

        </div>

        <!-- Pie del Reporte -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 print:border-gray-400 print:p-1">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 print:text-gray-700 print:text-xs">
                <p>Generado por: {{ $reportData['generated_by'] }}</p>
                <p>Total de turnos: {{ $reportData['appointments']->count() }}</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    if (window.location.search.includes('print=1')) {
        window.onload = function() {
            setTimeout(function() {
                window.print();
                window.addEventListener('afterprint', function() { window.close(); });
                setTimeout(function() { window.close(); }, 3000);
            }, 500);
        }
    }
</script>
@endpush
