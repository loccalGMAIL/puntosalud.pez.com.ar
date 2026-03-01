{{-- Alertas de estado de caja --}}
@if($cashStatus['is_closed'])
    <div class="mb-3 bg-red-50/50 dark:bg-red-900/10 border-l-2 border-red-300 dark:border-red-700 rounded-sm py-1.5 px-3 flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-red-500 dark:text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v7.5a2.25 2.25 0 002.25 2.25z" />
        </svg>
        <span class="text-xs text-red-700 dark:text-red-300">La caja ya fue cerrada hoy. <a href="{{ route('dashboard') }}" class="underline hover:no-underline">Ir al dashboard</a></span>
    </div>
@elseif($cashStatus['needs_opening'])
    <div class="mb-3 bg-amber-50/50 dark:bg-amber-900/10 border-l-2 border-amber-300 dark:border-amber-700 rounded-sm py-1.5 px-3 flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <span class="text-xs text-amber-700 dark:text-amber-300">La caja no está abierta. <a href="{{ route('dashboard') }}" class="underline hover:no-underline">Ir al dashboard</a></span>
    </div>
@endif
