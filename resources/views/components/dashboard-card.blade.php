@props(['title', 'value', 'icon', 'color' => 'emerald', 'stats' => []])

<div class="group relative overflow-hidden rounded-xl border border-{{ $color }}-200/50 bg-gradient-to-br from-white to-{{ $color }}-50/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-{{ $color }}-100/50 dark:border-{{ $color }}-800/30 dark:from-gray-900 dark:to-{{ $color }}-950/20 dark:hover:shadow-{{ $color }}-900/20">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
            <div class="mt-2 flex items-baseline gap-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ $value }}</p>
            </div>
            @if($stats)
                <div class="mt-3 flex gap-4 text-xs">
                    @foreach($stats as $stat)
                        <div class="flex items-center gap-1">
                            {!! $stat['icon'] ?? '' !!}
                            <span class="text-gray-600 dark:text-gray-400">{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-{{ $color }}-100 transition-all duration-300 group-hover:bg-{{ $color }}-200 group-hover:scale-110 dark:bg-{{ $color }}-900/30">
            {!! $icon !!}
        </div>
    </div>
    <!-- Decorative gradient -->
    <div class="absolute inset-0 bg-gradient-to-r from-{{ $color }}-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
</div>