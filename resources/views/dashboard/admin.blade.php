@extends('layouts.app')

@section('title', 'Dashboard Administrativo - ' . config('app.name'))
@section('mobileTitle', 'Dashboard Admin')

@section('content')
<div class="p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Resumen financiero y pendientes del centro.</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()?->canDo('reports'))
                <a href="{{ route('reports.expenses') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    Ver gastos
                </a>
            @endif
            @if(auth()->user()?->canAccessModule('expenses'))
                <a href="{{ route('expenses.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Gastos externos
                </a>
            @endif
        </div>
    </div>

    <!-- Resumen del mes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-baseline justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Resumen del mes</h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $summary['month_label'] }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Ingresos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Ingresos</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">${{ number_format($summary['income'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Gastos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Gastos</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">-${{ number_format($summary['total_expenses'], 2, ',', '.') }}</p>
                        {{-- <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Caja: -${{ number_format($summary['cash_expenses'], 2, ',', '.') }} · Externo: -${{ number_format($summary['external_expenses'], 2, ',', '.') }}</p> --}}
                    </div>
                </div>
            </div>

            <!-- Neto -->
            @php
                $netCls = $summary['net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Neto</p>
                        <p class="text-lg font-bold {{ $netCls }}">${{ number_format($summary['net'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 gastos del mes + Flujo 6 meses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top 5 gastos del mes</h3>
            </div>
            <div class="mt-3 space-y-2">
                @forelse($topExpenseTypes as $row)
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $row['icon'] }} {{ $row['name'] }}</div>
                        <div class="text-sm font-semibold text-red-600 dark:text-red-400">-${{ number_format($row['total'], 2, ',', '.') }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">Sin gastos en el período.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Flujo ultimos 6 meses</h3>
                </div>
                <a href="{{ route('reports.flujo-caja-mensual') }}" class="text-xs text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">Ver reporte</a>
            </div>
            <div class="mt-3 space-y-2">
                @forelse($flow['labels'] as $i => $label)
                    @php
                        $inc = $flow['income'][$i] ?? 0;
                        $exp = $flow['expenses'][$i] ?? 0;
                        $bal = $flow['net'][$i] ?? 0;
                        $balCls = $bal >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-400';
                        $balPrefix = $bal >= 0 ? '$' : '-$';
                    @endphp
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $label }}</div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div class="text-xs text-emerald-600/70 dark:text-emerald-400/60">${{ number_format($inc, 2, ',', '.') }}</div>
                                <div class="text-xs text-red-500/70 dark:text-red-400/60">-${{ number_format($exp, 2, ',', '.') }}</div>
                            </div>
                            <div class="text-sm font-semibold {{ $balCls }}">{{ $balPrefix }}{{ number_format(abs($bal), 2, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">Sin datos en el período.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection
