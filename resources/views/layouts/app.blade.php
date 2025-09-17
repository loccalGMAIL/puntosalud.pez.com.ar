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
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
                'title' => 'Cobro Pacientes',
                'href' => '/payments',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" /></svg>'
            ],
            [
                'title' => 'Caja',
                'href' => '/cash/daily',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>'
            ]
        ];

        // Agregar enlace de usuarios solo para administradores
        if (Auth::check() && Auth::user()->isAdmin()) {
            $navigationItems[] = [
                'title' => 'Usuarios',
                'href' => '/users',
                'icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
            ];
        }
    @endphp

    <!-- Sidebar Container -->
    <div x-data="{ 
        collapsed: false,
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
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="collapsed = true"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden">
        </div>

        <!-- Sidebar -->
        <div :class="{
            'translate-x-0': !collapsed,
            '-translate-x-full': collapsed && isMobile,
            'w-64': !collapsed,
            'w-16': collapsed && !isMobile
        }" 
        class="fixed left-0 top-0 z-50 h-full bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out flex flex-col">
            
            <!-- Header -->
            <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center w-full">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        @include('layouts.app-logo')
                    </a>
                    
                    <button @click="toggle()" 
                            class="ml-auto p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200 hidden lg:flex">
                        <svg :class="{ 'rotate-180': collapsed }" 
                             class="w-4 h-4 transition-transform duration-200" 
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
        <div :class="{
            'lg:ml-64': !collapsed,
            'lg:ml-16': collapsed
        }" 
        class="transition-all duration-300 ease-in-out min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Mobile header -->
            <div class="lg:hidden flex items-center justify-between p-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
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

    @stack('scripts')
</body>
</html>