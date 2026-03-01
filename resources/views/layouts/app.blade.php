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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Estado inicial del sidebar antes de Alpine */
        .sidebar-init { transform: translateX(-100%); width: 16rem; }
        @media (min-width: 768px) { .sidebar-init { transform: translateX(0); } }
        .content-init { margin-left: 0; }
        @media (min-width: 768px) { .content-init { margin-left: 16rem; } }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    @php
        $navigationItems = [
            [
                'title' => 'Dashboard',
                'href' => '/dashboard',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>'
            ],
            [
                'title' => 'Profesionales',
                'href' => '/professionals',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" /></svg>'
            ],
            [
                'title' => 'Pacientes',
                'href' => route('patients.index'),
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
            ],
            [
                'title' => 'Turnos',
                'href' => '/appointments',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5M8.25 12h.008v.008H8.25V12zm4.5 0h.008v.008H12.75V12zm4.5 0h.008v.008H17.25V12z" /></svg>'
            ],
            [
                'title' => 'Agenda',
                'href' => '/agenda',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5m-6 -7.5h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H18m-1.5 3H18m-1.5 3H18M9 7.5h1.5m-1.5 3h1.5m-1.5 3h1.5M6 7.5h1.5m-1.5 3h1.5m-1.5 3h1.5" /></svg>'
            ],
            [
                'title' => 'Cobros',
                'href' => '/payments',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" /></svg>'
            ],
            [
                'title' => 'Caja del Día',
                'href' => '/cash/daily',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>'
            ]
        ];

        // Agregar menú de reportes (solo si tiene módulo reports)
        if (Auth::check() && Auth::user()->canAccessModule('reports')) {
            $navigationItems[] = [
                'title' => 'Reportes',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>',
                'children' => [
                    // [
                    //     'title' => 'Agenda Diaria',
                    //     'href' => '/reports/daily-schedule'
                    // ],
                    [
                        'title' => 'Movimientos de Caja',
                        'href' => '/reports/cash'
                    ],
                    [
                        'title' => 'Análisis de Caja',
                        'href' => '/cash/report'
                    ],
                    [
                        'title' => 'Informe de Gastos',
                        'href' => '/reports/expenses'
                    ],
                    // [
                    //     'title' => 'Reporte de Profesionales',
                    //     'href' => '/reports/professionals'
                    // ],
                    // [
                    //     'title' => 'Reporte de Pacientes',
                    //     'href' => '/reports/patients'
                    // ]
                ]
            ];
        }

        // Agregar menú de configuración (módulo: configuration)
        if (Auth::check() && Auth::user()->canAccessModule('configuration')) {
            $navigationItems[] = [
                'title' => 'Configuración',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
                'children' => [
                    [
                        'title' => 'Usuarios',
                        'href' => '/users'
                    ],
                    [
                        'title' => 'Recesos y Feriados',
                        'href' => '/recesos'
                    ],
                ]
            ];
        }

        // Agregar menú de sistema (módulo: system)
        if (Auth::check() && Auth::user()->canAccessModule('system')) {
            $navigationItems[] = [
                'title' => 'Sistema',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>',
                'children' => [
                    [
                        'title' => 'Perfiles',
                        'href' => '/profiles'
                    ],
                    [
                        'title' => 'Tipos de Movimientos',
                        'href' => '/movement-types'
                    ],
                    [
                        'title' => 'Actividad',
                        'href' => '/activity-log'
                    ]
                ]
            ];
        }
    @endphp

    <!-- Sidebar Container -->
    <div x-data="{
        collapsed: window.innerWidth < 768,
        isMobile: window.innerWidth < 768,
        toggle() { this.collapsed = !this.collapsed }
    }"
    x-init="
        window.addEventListener('resize', () => {
            isMobile = window.innerWidth < 768;
            if (isMobile) collapsed = true;
        })
    ">
        <!-- Overlay for mobile -->
        <div x-show="!collapsed && isMobile"
             x-cloak
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="collapsed = true"
             class="fixed inset-0 z-40 bg-black/50">
        </div>

        <!-- Sidebar -->
        <div :style="{
            transform: (collapsed && isMobile) ? 'translateX(-100%)' : 'translateX(0)',
            width: collapsed && !isMobile ? '4rem' : '16rem'
        }"
        class="sidebar-init fixed left-0 top-0 z-50 h-full bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 ease-in-out">
            
            <!-- Header -->
            <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center w-full">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        @include('layouts.app-logo')
                    </a>
                    
                    <!-- Cerrar sidebar en móvil -->
                    <button @click="collapsed = true"
                            class="ml-auto p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 md:hidden">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <!-- Colapsar sidebar en desktop -->
                    <button @click="toggle()"
                            class="ml-auto p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hidden md:flex">
                        <svg :class="{ 'rotate-180': collapsed }"
                             class="w-4 h-4"
                             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <div class="flex-1 overflow-y-auto">
                    @include('layouts.nav-main', ['items' => $navigationItems])
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-2">
                @include('layouts.nav-user')
            </div>
        </div>

        <!-- Main content wrapper -->
        <div :style="{ marginLeft: isMobile ? '0' : (collapsed ? '4rem' : '16rem') }"
        class="content-init min-h-screen bg-gray-50 dark:bg-gray-900 transition-[margin] duration-300 ease-in-out">
            <!-- Mobile header -->
            <div class="md:hidden flex items-center justify-between p-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <button @click="collapsed = false" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">@yield('mobileTitle', 'Dashboard')</h1>
            </div>

            <!-- Page content -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Modal del Sistema -->
    <x-system-modal />

    <!-- Toast Notifications -->
    <x-toast-notifications />

    @stack('scripts')
</body>
</html>