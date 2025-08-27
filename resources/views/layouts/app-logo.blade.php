@props(['size' => 'default'])

@php
$sizeClasses = match($size) {
    'sm' => 'size-6',
    'lg' => 'size-12', 
    default => 'size-8'
};

$iconSizeClasses = match($size) {
    'sm' => 'size-4',
    'lg' => 'size-8',
    default => 'size-5'  
};

$textSizeClasses = match($size) {
    'sm' => 'text-xs',
    'lg' => 'text-lg',
    default => 'text-sm'
};
@endphp

<div class="flex items-center">
    <!-- Logo Icon -->
    <div class="flex {{ $sizeClasses }} items-center justify-center rounded-md bg-emerald-600 text-white">
        <!-- Medical Cross Icon -->
        <svg class="{{ $iconSizeClasses }} fill-current" viewBox="0 0 24 24">
            <path d="M12 2C13.1 2 14 2.9 14 4V8H18C19.1 8 20 8.9 20 10V14C20 15.1 19.1 16 18 16H14V20C14 21.1 13.1 22 12 22H10C8.9 22 8 21.1 8 20V16H4C2.9 16 2 15.1 2 14V10C2 8.9 2.9 8 4 8H8V4C8 2.9 8.9 2 10 2H12Z"/>
        </svg>
    </div>
    
    <!-- Logo Text -->
    <div class="ml-3 flex-1 text-left {{ $textSizeClasses }}" x-show="!collapsed">
        <div class="truncate leading-tight font-semibold text-emerald-700 dark:text-emerald-400">
            Punto 
            <span class="text-emerald-800 dark:text-emerald-300 font-bold">SALUD</span>
        </div>
    </div>
</div>