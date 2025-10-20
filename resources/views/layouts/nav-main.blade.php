@props(['items' => []])

<div class="px-2 py-4">
    <!-- Group Label -->
    <div class="px-3 py-2" x-show="!collapsed" x-cloak>
        <h2 class="mb-2 px-2 text-xs font-semibold tracking-tight text-gray-500 dark:text-gray-400 uppercase">
            Menú
        </h2>
    </div>

    <!-- Menu Items -->
    <nav class="space-y-1">
        @foreach($items as $item)
            <a href="{{ $item['href'] }}" 
               class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 
                      {{ request()->is(ltrim($item['href'], '/') . '*') 
                         ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' 
                         : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
               x-data="{ tooltip: false }"
               @mouseenter="if (collapsed) tooltip = true"
               @mouseleave="tooltip = false">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-5 h-5 mr-3">
                    {!! $item['icon'] !!}
                </div>
                
                <!-- Text -->
                <span x-show="!collapsed" x-cloak class="flex-1">{{ $item['title'] }}</span>
                
                <!-- Tooltip for collapsed state -->
                <div x-show="collapsed && tooltip"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-2"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-2"
                     class="fixed left-20 z-50 px-3 py-2 bg-gray-900 text-white text-sm rounded-md shadow-lg pointer-events-none"
                     style="margin-top: -10px;">
                    {{ $item['title'] }}
                    <!-- Arrow -->
                    <div class="absolute right-full top-1/2 -translate-y-1/2">
                        <div class="border-4 border-transparent border-r-gray-900"></div>
                    </div>
                </div>
            </a>
        @endforeach
    </nav>
</div>