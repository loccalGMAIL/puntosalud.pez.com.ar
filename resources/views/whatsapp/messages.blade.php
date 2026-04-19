@extends('layouts.app')

@section('title', 'WhatsApp - Mensajes - ' . config('app.name'))
@section('mobileTitle', 'WA Mensajes')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4"
     x-data="{
         modal: false,
         selected: { patient: '', phone: '', date: '', professional: '', message: '', type: '', status: '', sent_at: '', error: '' },
         open(data) { this.selected = data; this.modal = true; },
         typeLabel(t) { return { reminder: 'Recordatorio', creation: 'Confirmación', cancellation: 'Cancelación' }[t] ?? t; },
         typeColor(t) { return { reminder: 'blue', creation: 'emerald', cancellation: 'red' }[t] ?? 'gray'; }
     }"
     @keydown.escape.window="modal = false">

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
                Historial de mensajes enviados por WhatsApp
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
        <form method="GET" action="{{ route('whatsapp.messages') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Filtrar:</label>
            <select name="status" onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="" {{ ! request('status') ? 'selected' : '' }}>Todos los estados</option>
                <option value="sent"    {{ request('status') === 'sent'    ? 'selected' : '' }}>Enviados</option>
                <option value="failed"  {{ request('status') === 'failed'  ? 'selected' : '' }}>Fallidos</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendientes</option>
            </select>
            <select name="type" onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="" {{ ! request('type') ? 'selected' : '' }}>Todos los tipos</option>
                <option value="reminder"     {{ request('type') === 'reminder'     ? 'selected' : '' }}>Recordatorio</option>
                <option value="creation"     {{ request('type') === 'creation'     ? 'selected' : '' }}>Confirmación</option>
                <option value="cancellation" {{ request('type') === 'cancellation' ? 'selected' : '' }}>Cancelación</option>
            </select>
            @if (request('status') || request('type'))
            <a href="{{ route('whatsapp.messages') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Limpiar filtros
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
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Los mensajes aparecerán aquí cuando se envíen.</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Paciente</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Teléfono</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Turno</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Enviado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Mensaje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($messages as $msg)
                @php
                    $msgData = [
                        'patient'      => $msg->patient?->full_name ?? '-',
                        'phone'        => $msg->phone,
                        'date'         => $msg->appointment?->appointment_date->format('d/m/Y H:i') ?? '-',
                        'professional' => $msg->appointment?->professional?->full_name ?? '-',
                        'message'      => $msg->message,
                        'type'         => $msg->type,
                        'status'       => $msg->status,
                        'sent_at'      => $msg->sent_at?->format('d/m/Y H:i') ?? '-',
                        'error'        => $msg->error ?? '',
                    ];
                @endphp
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
                        <p class="text-gray-900 dark:text-white">{{ $msg->appointment->appointment_date->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $msg->appointment->professional?->full_name ?? '' }}</p>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $typeLabels = ['reminder' => 'Recordatorio', 'creation' => 'Confirmación', 'cancellation' => 'Cancelación'];
                            $typeColors = ['reminder' => 'blue', 'creation' => 'emerald', 'cancellation' => 'red'];
                            $tColor = $typeColors[$msg->type] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            @if($tColor === 'blue') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                            @elseif($tColor === 'emerald') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                            @elseif($tColor === 'red') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                            @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 @endif">
                            {{ $typeLabels[$msg->type] ?? $msg->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($msg->status === 'sent')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Enviado
                        </span>
                        @elseif ($msg->status === 'failed')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Fallido
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pendiente
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                        {{ $msg->sent_at ? $msg->sent_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <button type="button"
                                data-msg="{{ json_encode($msgData, JSON_UNESCAPED_UNICODE) }}"
                                @click="open(JSON.parse($el.dataset.msg))"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Ver
                        </button>
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
        @php
            $typeLabels = ['reminder' => 'Recordatorio', 'creation' => 'Confirmación', 'cancellation' => 'Cancelación'];
            $typeColors = ['reminder' => 'blue', 'creation' => 'emerald', 'cancellation' => 'red'];
            $tColor     = $typeColors[$msg->type] ?? 'gray';
            $msgData    = [
                'patient'      => $msg->patient?->full_name ?? '-',
                'phone'        => $msg->phone,
                'date'         => $msg->appointment?->appointment_date->format('d/m/Y H:i') ?? '-',
                'professional' => $msg->appointment?->professional?->full_name ?? '-',
                'message'      => $msg->message,
                'type'         => $msg->type,
                'status'       => $msg->status,
                'sent_at'      => $msg->sent_at?->format('d/m/Y H:i') ?? '-',
                'error'        => $msg->error ?? '',
            ];
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $msg->patient?->full_name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $msg->phone }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        @if($tColor === 'blue') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($tColor === 'emerald') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                        @elseif($tColor === 'red') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                        @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 @endif">
                        {{ $typeLabels[$msg->type] ?? $msg->type }}
                    </span>
                    @if ($msg->status === 'sent')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Enviado</span>
                    @elseif ($msg->status === 'failed')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Fallido</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pendiente</span>
                    @endif
                </div>
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
            <button type="button"
                    data-msg="{{ json_encode($msgData, JSON_UNESCAPED_UNICODE) }}"
                    @click="open(JSON.parse($el.dataset.msg))"
                    class="mt-2 inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Ver mensaje
            </button>
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

    <!-- Modal ver mensaje -->
    <div x-show="modal"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @click.self="modal = false"
         style="display:none">
        <div x-show="modal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">

            <!-- Header modal -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm" x-text="selected.patient"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono" x-text="selected.phone"></p>
                    </div>
                </div>
                <button @click="modal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body modal -->
            <div class="px-5 py-4 space-y-4">

                <!-- Badges tipo + estado -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                              'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': selected.type === 'reminder',
                              'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': selected.type === 'creation',
                              'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': selected.type === 'cancellation'
                          }"
                          x-text="typeLabel(selected.type)">
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                              'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': selected.status === 'sent',
                              'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': selected.status === 'failed',
                              'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': selected.status === 'pending'
                          }">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="{
                                  'bg-emerald-500': selected.status === 'sent',
                                  'bg-red-500': selected.status === 'failed',
                                  'bg-amber-500 animate-pulse': selected.status === 'pending'
                              }"></span>
                        <span x-text="{ sent: 'Enviado', failed: 'Fallido', pending: 'Pendiente' }[selected.status]"></span>
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500" x-show="selected.sent_at !== '-'" x-text="'· ' + selected.sent_at"></span>
                </div>

                <!-- Info del turno -->
                <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                    <p><span class="font-medium text-gray-700 dark:text-gray-300">Turno:</span> <span x-text="selected.date"></span></p>
                    <p><span class="font-medium text-gray-700 dark:text-gray-300">Profesional:</span> <span x-text="selected.professional"></span></p>
                </div>

                <!-- Burbuja de mensaje -->
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm p-4 relative">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="selected.message"></p>
                    <div class="flex justify-end mt-1">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500" x-text="selected.sent_at !== '-' ? selected.sent_at : ''"></span>
                    </div>
                </div>

                <!-- Error si falló -->
                <div x-show="selected.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2">
                    <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-0.5">Error</p>
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="selected.error"></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button @click="modal = false"
                        class="px-4 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
