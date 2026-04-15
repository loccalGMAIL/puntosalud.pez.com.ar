@extends('layouts.app')

@section('title', 'WhatsApp - Mensajes - ' . config('app.name'))
@section('mobileTitle', 'WA Mensajes')

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
                <span>Mensajes</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Mensajes enviados
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Historial de recordatorios enviados por WhatsApp
            </p>
        </div>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-emerald-600 dark:text-emerald-400 uppercase tracking-wide mb-1">Enviados</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['sent']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">Fallidos</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['failed']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-1">Pendientes</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['pending']) }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <form method="GET" action="{{ route('whatsapp.messages') }}" class="flex items-center gap-3">
            <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Filtrar por estado:</label>
            <select id="status" name="status"
                    onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="" {{ ! request('status') ? 'selected' : '' }}>Todos</option>
                <option value="sent"    {{ request('status') === 'sent'    ? 'selected' : '' }}>Enviados</option>
                <option value="failed"  {{ request('status') === 'failed'  ? 'selected' : '' }}>Fallidos</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendientes</option>
            </select>
            @if (request('status'))
            <a href="{{ route('whatsapp.messages') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Limpiar filtro
            </a>
            @endif
        </form>
    </div>

    <!-- Tabla desktop -->
    <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($messages->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">No hay mensajes</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                Los recordatorios aparecerán aquí cuando se envíen.
            </p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Paciente</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Teléfono</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Turno</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Enviado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($messages as $msg)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $msg->id }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $msg->patient?->full_name ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">
                        {{ $msg->phone }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($msg->appointment)
                        <p class="text-gray-900 dark:text-white">
                            {{ $msg->appointment->appointment_date->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $msg->appointment->professional?->full_name ?? '' }}
                        </p>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($msg->status === 'sent')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Enviado
                        </span>
                        @elseif ($msg->status === 'failed')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Fallido
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Pendiente
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                        {{ $msg->sent_at ? $msg->sent_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($msg->error)
                        <span class="text-xs text-red-600 dark:text-red-400 truncate max-w-xs block" title="{{ $msg->error }}">
                            {{ Str::limit($msg->error, 60) }}
                        </span>
                        @elseif ($msg->status === 'sent')
                        <span class="text-xs text-emerald-600 dark:text-emerald-400">OK</span>
                        @else
                        <span class="text-gray-400">-</span>
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
        @forelse ($messages as $msg)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $msg->patient?->full_name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $msg->phone }}</p>
                </div>
                @if ($msg->status === 'sent')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Enviado</span>
                @elseif ($msg->status === 'failed')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Fallido</span>
                @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pendiente</span>
                @endif
            </div>
            @if ($msg->appointment)
            <p class="text-xs text-gray-600 dark:text-gray-400">
                Turno: {{ $msg->appointment->appointment_date->format('d/m/Y H:i') }}
                — {{ $msg->appointment->professional?->full_name ?? '' }}
            </p>
            @endif
            @if ($msg->sent_at)
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Enviado: {{ $msg->sent_at->format('d/m/Y H:i') }}</p>
            @endif
            @if ($msg->error)
            <p class="text-xs text-red-500 dark:text-red-400 mt-1 truncate">{{ $msg->error }}</p>
            @endif
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">No hay mensajes para mostrar.</p>
        </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if ($messages->hasPages())
    <div class="flex justify-center">
        {{ $messages->links() }}
    </div>
    @endif

</div>
@endsection
