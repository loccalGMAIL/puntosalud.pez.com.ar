@props(['title', 'subtitle' => null])

<div class="flex items-center justify-between gap-2">
    <!-- Logo -->
    <div class="flex-shrink-0">
        <img src="{{ asset('logo.png') }}" alt="Logo PuntoSalud"
            class="w-32 h-32 print:w-24 print:h-24 object-contain">
    </div>

    <!-- Información del Reporte -->
    <div class="flex-1 text-center space-y-0.5">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 print:text-gray-700 print:text-sm">
            {{ $title }}</h3>
        @if($subtitle)
            <p class="text-gray-600 dark:text-gray-400 print:text-gray-700 print:text-xs">{{ $subtitle }}</p>
        @endif
        <p class="text-sm text-gray-500 dark:text-gray-500 print:text-gray-500 print:text-xs">Generado:
            {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Espacio para balance visual -->
    <div class="flex-shrink-0 w-32 print:w-24"></div>
</div>
