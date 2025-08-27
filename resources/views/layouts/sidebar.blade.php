@props([
    'collapsible' => true,
    'collapsed' => false
])

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
                {{ $header ?? '' }}
                
                @if($collapsible)
                <button @click="toggle()" 
                        class="ml-auto p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200 hidden lg:flex">
                    <svg :class="{ 'rotate-180': collapsed }" 
                         class="w-4 h-4 transition-transform duration-200" 
                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                {{ $slot }}
            </div>
        </div>

        <!-- Footer -->
        @isset($footer)
        <div class="border-t border-gray-200 dark:border-gray-700 p-2">
            {{ $footer }}
        </div>
        @endisset
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
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $mobileTitle ?? 'Dashboard' }}</h1>
        </div>

        <!-- Page content -->
        <main>
            {{ $content ?? '' }}
        </main>
    </div>
</div>