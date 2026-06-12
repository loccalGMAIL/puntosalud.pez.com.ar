@extends('layouts.app')

@section('title', 'WhatsApp - Recordatorios pendientes - ' . config('app.name'))
@section('mobileTitle', 'WA Pendientes')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('whatsapp.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">WhatsApp</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Recordatorios pendientes</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Recordatorios pendientes
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Turnos que aún no recibieron recordatorio, con su horario de envío programado (anticipación: {{ $hoursBefore }}h)
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-1">Pendientes en ventana</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($paginated->total()) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Próximo turno</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $paginated->isNotEmpty() ? $paginated->first()->appointment_date->format('d/m/Y H:i') : '—' }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Próximo envío</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $paginated->isNotEmpty() ? $paginated->first()->dispatch_time->format('d/m/Y H:i') : '—' }}
            </p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <form method="GET" action="{{ route('whatsapp.reminders-status') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Profesional</label>
                <select name="professional_id" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">Todos los profesionales</option>
                    @foreach ($professionals as $prof)
                    <option value="{{ $prof->id }}" {{ request('professional_id') == $prof->id ? 'selected' : '' }}>
                        {{ $prof->last_name }}, {{ $prof->first_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            </div>
            <button type="submit"
                    class="px-4 py-1.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
                Filtrar
            </button>
            @if (request('professional_id') || request('date_from') || request('date_to'))
            <a href="{{ route('whatsapp.reminders-status') }}"
               class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 self-end pb-1.5">
                Limpiar filtros
            </a>
            @endif
        </form>
    </div>

    <!-- Tabla desktop -->
    <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($paginated->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">Sin recordatorios pendientes</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Todos los turnos próximos ya recibieron su recordatorio.</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Paciente</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Teléfono</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Turno</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Profesional</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Hora ideal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Envío programado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($paginated as $appointment)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $appointment->patient?->full_name ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">
                        {{ $appointment->patient?->phone ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-gray-900 dark:text-white">{{ $appointment->appointment_date->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $appointment->appointment_date->format('H:i') }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                        {{ $appointment->professional?->full_name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs font-mono">
                        {{ $appointment->ideal_time->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white text-xs font-mono">
                        {{ $appointment->dispatch_time->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($appointment->dispatch_status === 'excluded')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            {{ $appointment->dispatch_label }}
                        </span>
                        @elseif ($appointment->dispatch_status === 'overdue')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ $appointment->dispatch_label }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            {{ $appointment->dispatch_label }}
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Cards mobile -->
    <div class="md:hidden space-y-3">
        @forelse ($paginated as $appointment)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $appointment->patient?->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $appointment->patient?->phone ?? '—' }}</p>
                </div>
                @if ($appointment->dispatch_status === 'excluded')
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                    Excluido
                </span>
                @elseif ($appointment->dispatch_status === 'overdue')
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                    Atrasado
                </span>
                @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    En cola
                </span>
                @endif
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400">
                Turno: {{ $appointment->appointment_date->format('d/m/Y H:i') }}
                — {{ $appointment->professional?->full_name ?? '' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                Envío: {{ $appointment->dispatch_time->format('d/m/Y H:i') }}
                <span class="text-gray-400">({{ $appointment->dispatch_label }})</span>
            </p>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">Sin recordatorios pendientes.</p>
        </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if ($paginated->hasPages())
    <div class="flex justify-center">
        {{ $paginated->links() }}
    </div>
    @endif

</div>
@endsection
