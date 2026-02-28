{{-- Alertas de estado de caja --}}
@if($cashStatus['is_closed'])
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v7.5a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <div>
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Caja Cerrada</h3>
                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                    La caja del día está cerrada. No se pueden crear turnos para hoy ni procesar pagos inmediatos.
                    <a href="{{ route('cash.daily') }}" class="underline hover:no-underline">Ir a Caja</a>
                </p>
            </div>
        </div>
    </div>
@elseif($cashStatus['needs_opening'])
    <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div>
                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Caja Sin Abrir</h3>
                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                    La caja del día no ha sido abierta. Debe abrir la caja antes de crear turnos para hoy o procesar pagos.
                    <a href="{{ route('cash.daily') }}" class="underline hover:no-underline">Abrir Caja</a>
                </p>
            </div>
        </div>
    </div>
@endif
