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

    <img src="{{ asset('logo.png') }}" alt="Logo Punto Salud">
    

</div>