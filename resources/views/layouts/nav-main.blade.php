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
            @if(isset($item['children']))
                <!-- Submenu Item -->
                <div x-data="{ open: {{ request()->is(collect($item['children'])->pluck('href')->map(fn($h) => ltrim($h, '/').'*')->implode('|')) ? 'true' : 'false' }}, tooltip: false }">
                    <!-- Submenu Trigger -->
                    <button @click="if (!collapsed) open = !open"
                            @mouseenter="if (collapsed) tooltip = true"
                            @mouseleave="tooltip = false"
                            class="w-full group flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            :class="open && !collapsed ? 'bg-gray-50 dark:bg-gray-800/50' : ''">

                        <!-- Icon -->
                        <div class="flex items-center justify-center w-5 h-5 mr-3">
                            {!! $item['icon'] !!}
                        </div>

                        <!-- Text -->
                        <span x-show="!collapsed" x-cloak class="flex-1 text-left">{{ $item['title'] }}</span>

                        <!-- Chevron -->
                        <svg x-show="!collapsed" x-cloak :class="{ 'rotate-90': open }" class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>

                        <!-- Tooltip/Popup for collapsed state -->
                        <div x-show="collapsed && tooltip"
                             x-cloak
                             @mouseenter="tooltip = true"
                             @mouseleave="tooltip = false"
                             class="fixed left-20 z-50 bg-gray-900 text-white text-sm rounded-md shadow-lg min-w-[200px]"
                             style="margin-top: -10px;">
                            <!-- Submenu Title -->
                            <div class="px-3 py-2 font-semibold border-b border-gray-700">
                                {{ $item['title'] }}
                            </div>
                            <!-- Submenu Items -->
                            <div class="py-1">
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['href'] }}"
                                       class="block px-3 py-2 hover:bg-gray-800 transition-colors
                                              {{ request()->is(ltrim($child['href'], '/') . '*') ? 'bg-gray-800 text-emerald-400' : '' }}">
                                        {{ $child['title'] }}
                                    </a>
                                @endforeach
                            </div>
                            <!-- Arrow -->
                            <div class="absolute right-full top-3">
                                <div class="border-4 border-transparent border-r-gray-900"></div>
                            </div>
                        </div>
                    </button>

                    <!-- Submenu Items (expanded) -->
                    <div x-show="open && !collapsed" x-cloak class="mt-1 space-y-1 ml-8">
                        @foreach($item['children'] as $child)
                            <a href="{{ $child['href'] }}"
                               class="flex items-center px-3 py-2 rounded-lg text-sm font-medium
                                      {{ request()->is(ltrim($child['href'], '/') . '*')
                                         ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
                                         : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50' }}">
                                {{ $child['title'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Regular Menu Item -->
                <a href="{{ $item['href'] }}"
                   class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium
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
                         class="fixed left-20 z-50 px-3 py-2 bg-gray-900 text-white text-sm rounded-md shadow-lg pointer-events-none"
                         style="margin-top: -10px;">
                        {{ $item['title'] }}
                        <!-- Arrow -->
                        <div class="absolute right-full top-1/2 -translate-y-1/2">
                            <div class="border-4 border-transparent border-r-gray-900"></div>
                        </div>
                    </div>
                </a>
            @endif
        @endforeach
    </nav>
</div>