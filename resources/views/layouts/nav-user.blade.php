@props(['user' => null])

@php
$user = $user ?? auth()->user();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" 
            class="w-full flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors duration-200">
        
        <!-- Avatar -->
        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-600 text-white font-medium text-xs mr-3 flex-shrink-0">
            @if($user->avatar ?? null)
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full rounded-full object-cover">
            @else
                {{ strtoupper(substr($user->name, 0, 2)) }}
            @endif
        </div>
        
        <!-- User Info -->
        <div x-show="!collapsed" class="flex-1 text-left min-w-0">
            <div class="font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</div>
        </div>
        
        <!-- Chevron -->
        <svg x-show="!collapsed" 
             :class="{ 'rotate-180': open }" 
             class="w-4 h-4 ml-2 transition-transform duration-200 flex-shrink-0" 
             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open && !collapsed" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="open = false"
         class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
        
        <!-- User Info Header -->
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
            <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
        </div>
        
        <!-- Menu Items -->
        <div class="py-1">
            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Mi Perfil
                </div>
            </a>
            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Cerrar Sesión
                    </div>
                </button>
            </form>
        </div>
    </div>

    <!-- Tooltip for collapsed state -->
    <div x-show="collapsed && open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-2"
         @click.away="open = false"
         class="fixed left-20 bottom-4 z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 min-w-64">
        
        <!-- User Info -->
        <div class="flex items-center mb-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-600 text-white font-medium mr-3">
                @if($user->avatar ?? null)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full rounded-full object-cover">
                @else
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
            </div>
            <div>
                <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="space-y-1">
            <a href="{{ route('profile') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-md">
                <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Mi Perfil
            </a>
            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 rounded-md">
                    <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
        
        <!-- Arrow -->
        <div class="absolute right-full top-1/2 -translate-y-1/2 -mr-1">
            <div class="border-4 border-transparent border-r-white dark:border-r-gray-800"></div>
        </div>
    </div>
</div>