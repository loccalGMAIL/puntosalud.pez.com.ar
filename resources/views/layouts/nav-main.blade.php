@props(['items' => []])

<div class="px-2 py-4">

    <!-- Menu Items -->
    <nav class="space-y-1">
        @foreach($items as $item)
            @if(isset($item['children']))
                <!-- Submenu Item -->
                @php
                    $collectHrefs = function($children) use (&$collectHrefs) {
                        $hrefs = [];
                        foreach ($children as $child) {
                            if (!empty($child['children'])) {
                                $hrefs = array_merge($hrefs, $collectHrefs($child['children']));
                            } elseif (isset($child['href'])) {
                                $hrefs[] = ltrim($child['href'], '/');
                            }
                        }
                        return $hrefs;
                    };
                    $allChildHrefs = $collectHrefs($item['children']);
                    $isOpenState = !empty($allChildHrefs) && request()->is(...$allChildHrefs) ? 'true' : 'false';
                @endphp
                <div x-data="{ open: {{ $isOpenState }}, tooltip: false }">
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
                                    @if(isset($child['separator']))
                                        <div class="border-t border-gray-700 my-1"></div>
                                    @elseif(isset($child['submenu']) || isset($child['group']))
                                        <div class="px-3 pt-2 pb-0.5">
                                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $child['title'] }}</span>
                                        </div>
                                        @foreach($child['children'] as $subItem)
                                            <a href="{{ $subItem['href'] }}"
                                               class="block px-4 py-1.5 hover:bg-gray-800 transition-colors
                                                      {{ request()->is(ltrim($subItem['href'], '/')) ? 'bg-gray-800 text-emerald-400' : '' }}">
                                                {{ $subItem['title'] }}
                                            </a>
                                        @endforeach
                                    @else
                                        <a href="{{ $child['href'] }}"
                                           class="block px-3 py-2 hover:bg-gray-800 transition-colors
                                                  {{ request()->is(ltrim($child['href'], '/')) ? 'bg-gray-800 text-emerald-400' : '' }}">
                                            {{ $child['title'] }}
                                        </a>
                                    @endif
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
                            @if(isset($child['separator']))
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1 mx-3"></div>
                            @elseif(isset($child['submenu']))
                                {{-- Colapsable de tercer nivel --}}
                                @php
                                    $subHrefs  = array_map(fn($c) => ltrim($c['href'], '/'), $child['children']);
                                    $subIsOpen = !empty($subHrefs) && request()->is(...$subHrefs) ? 'true' : 'false';
                                @endphp
                                <div x-data="{ subOpen: {{ $subIsOpen }} }">
                                    <button @click="subOpen = !subOpen"
                                            class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-sm font-medium
                                                   {{ !empty($subHrefs) && request()->is(...$subHrefs) ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50' }}">
                                        <span>{{ $child['title'] }}</span>
                                        <svg :class="{ 'rotate-90': subOpen }" class="w-3 h-3 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" class="ml-3 mt-0.5 space-y-0.5">
                                        @foreach($child['children'] as $subItem)
                                            <a href="{{ $subItem['href'] }}"
                                               class="flex items-center px-3 py-1.5 rounded-lg text-sm font-medium
                                                      {{ request()->is(ltrim($subItem['href'], '/'))
                                                         ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
                                                         : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50' }}">
                                                {{ $subItem['title'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif(isset($child['group']))
                                <div class="px-3 pt-2 pb-0.5">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $child['title'] }}</span>
                                </div>
                                @foreach($child['children'] as $groupItem)
                                    <a href="{{ $groupItem['href'] }}"
                                       class="flex items-center px-3 py-1.5 rounded-lg text-sm font-medium
                                              {{ request()->is(ltrim($groupItem['href'], '/'))
                                                 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
                                                 : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50' }}">
                                        {{ $groupItem['title'] }}
                                    </a>
                                @endforeach
                            @else
                                <a href="{{ $child['href'] }}"
                                   class="flex items-center px-3 py-2 rounded-lg text-sm font-medium
                                          {{ request()->is(ltrim($child['href'], '/'))
                                             ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
                                             : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50' }}">
                                    {{ $child['title'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Regular Menu Item -->
                <a href="{{ $item['href'] }}"
                   class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium
                          {{ request()->is(ltrim($item['href'], '/'))
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
