@extends('layouts.app')

@section('title', 'Registro de Actividad - ' . config('app.name'))
@section('mobileTitle', 'Actividad')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="activityLogPage()">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>Registro de Actividad</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Registro de Actividad
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Historial de todas las operaciones realizadas en el sistema
                </p>
            </div>
        </div>

        <!-- Cards de estadísticas -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hoy</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['today']) }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">acciones hoy</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Esta semana</dt>
                        <dd class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['week']) }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">acciones</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Este mes</dt>
                        <dd class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['month']) }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">acciones</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios activos hoy</dt>
                        <dd class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['active_users'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">con actividad</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <form method="GET" action="{{ route('activity-log.index') }}" class="flex flex-wrap gap-3 items-end">
                <!-- Desde -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Desde</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Hasta -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Hasta</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Usuario -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Usuario</label>
                    <select name="user_id"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Acción -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Acción</label>
                    <select name="action"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todas</option>
                        <option value="created"  {{ ($filters['action'] ?? '') === 'created'  ? 'selected' : '' }}>Creó</option>
                        <option value="updated"  {{ ($filters['action'] ?? '') === 'updated'  ? 'selected' : '' }}>Modificó</option>
                        <option value="deleted"  {{ ($filters['action'] ?? '') === 'deleted'  ? 'selected' : '' }}>Eliminó</option>
                        <option value="login"    {{ ($filters['action'] ?? '') === 'login'    ? 'selected' : '' }}>Inicio sesión</option>
                        <option value="logout"   {{ ($filters['action'] ?? '') === 'logout'   ? 'selected' : '' }}>Cerró sesión</option>
                    </select>
                </div>

                <!-- Módulo -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Módulo</label>
                    <select name="subject_type"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todos</option>
                        @foreach($moduleNames as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['subject_type'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones -->
                <div class="flex gap-2 pb-0.5">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Filtrar
                    </button>
                    <a href="{{ route('activity-log.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $logs->total() }} {{ $logs->total() === 1 ? 'registro' : 'registros' }}
                @if($logs->total() > 0)
                    — página {{ $logs->currentPage() }} de {{ $logs->lastPage() }}
                @endif
            </span>
        </div>

        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No hay registros</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">No se encontraron actividades con los filtros aplicados</p>
            </div>
        @else
            <!-- Mobile: Cards (md:hidden) -->
            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($logs as $log)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </span>
                        @include('activity-log._action-badge', ['action' => $log->action])
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $log->user?->name ?? 'Sistema' }}
                    </p>
                    @if($log->subject_type)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span class="font-medium">{{ $moduleNames[$log->subject_type] ?? $log->subject_type }}</span>
                            @if($log->subject_description)
                                — {{ $log->subject_description }}
                            @endif
                        </p>
                    @endif
                    @if($log->ip_address)
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">IP: {{ $log->ip_address }}</p>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Desktop: Table (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha / Hora</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acción</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Módulo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-600 dark:text-gray-400">
                                {{ $log->created_at->format('d/m/Y') }}<br>
                                <span class="text-gray-400 dark:text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $log->user?->name ?? 'Sistema' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @include('activity-log._action-badge', ['action' => $log->action])
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $moduleNames[$log->subject_type] ?? $log->subject_type ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                {{ $log->subject_description ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-400 dark:text-gray-500">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
            @endif
        @endif
    </div>
</div>

<script>
function activityLogPage() {
    return {
        init() {
            // Componente simple, sin estado adicional necesario
        }
    };
}
</script>
@endsection
