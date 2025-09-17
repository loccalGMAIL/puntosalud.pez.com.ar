<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
</head>
<body class="bg-white print:bg-white">
    <!-- Header para pantalla -->
    <header class="print:hidden bg-gray-50 border-b border-gray-200 p-4 no-print">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">@yield('title', 'Reporte')</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18.6A4.5 4.5 0 002.25 22.5h15A4.5 4.5 0 0021.75 18.6l-.64-4.771m-10.56 0a42.415 42.415 0 0110.56 0" />
                    </svg>
                    Imprimir
                </button>
                <a href="{{ route('cash.daily') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="min-h-screen print:min-h-0">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

<style>
@media print {
    @page {
        margin: 1.5cm;
        size: A4;
    }

    body {
        font-size: 12px;
        line-height: 1.4;
        color: black !important;
        background: white !important;
        margin: 0;
        padding: 0;
    }

    /* Ocultar elementos no imprimibles */
    .no-print,
    .print\\:hidden,
    header,
    nav,
    button,
    .btn,
    [onclick],
    [href*="javascript"],
    .screen-only {
        display: none !important;
    }

    /* Ajustar contenido para impresión */
    main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        box-shadow: none !important;
        border: none !important;
    }

    /* Estilos específicos para impresión */
    .print\\:p-2 {
        padding: 0.5rem !important;
    }

    .print\\:p-4 {
        padding: 1rem !important;
    }

    .print\\:text-black {
        color: black !important;
    }

    .print\\:text-gray-600 {
        color: #4b5563 !important;
    }

    .print\\:text-gray-700 {
        color: #374151 !important;
    }

    .print\\:text-gray-800 {
        color: #1f2937 !important;
    }

    .print\\:bg-gray-100 {
        background-color: #f3f4f6 !important;
    }

    .print\\:bg-yellow-100 {
        background-color: #fef3c7 !important;
    }

    .print\\:border {
        border-width: 1px !important;
    }

    .print\\:border-gray-300 {
        border-color: #d1d5db !important;
    }

    .print\\:border-gray-400 {
        border-color: #9ca3af !important;
    }

    .print\\:border-yellow-400 {
        border-color: #fbbf24 !important;
    }

    .print\\:shadow-none {
        box-shadow: none !important;
    }

    .print\\:border-none {
        border: none !important;
    }

    .print\\:divide-gray-400 > :not([hidden]) ~ :not([hidden]) {
        border-color: #9ca3af !important;
    }

    /* Mejorar contraste para impresión */
    .text-green-600,
    .text-green-400 {
        color: #059669 !important;
    }

    .text-red-600,
    .text-red-400 {
        color: #dc2626 !important;
    }

    .text-blue-600,
    .text-blue-400 {
        color: #2563eb !important;
    }

    /* Asegurar que las tablas se vean bien */
    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    th, td {
        border: 1px solid #d1d5db !important;
        padding: 0.5rem !important;
    }

    /* Evitar saltos de página en elementos importantes */
    .page-break-inside-avoid {
        page-break-inside: avoid;
    }
}
</style>