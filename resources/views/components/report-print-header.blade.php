@props(['title', 'subtitle' => null, 'generatedAt' => null, 'isPdf' => false])

@php
    $generatedAt = $generatedAt ?? now();

    $logoSrc = center_image('logo', '');
    $logoDataUri = null;

    if ($isPdf) {
        // Prefer raster formats for better DomPDF compatibility.
        $extensions = ['png', 'jpg', 'jpeg', 'webp', 'svg'];

        foreach ($extensions as $ext) {
            $path = public_path("center/logo.{$ext}");
            if (! file_exists($path)) {
                continue;
            }

            $mime = match ($ext) {
                'png'  => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                default => 'application/octet-stream',
            };

            $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            break;
        }
    }
@endphp

@if($isPdf)
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 90px; vertical-align: middle; padding: 0 10px 0 0;">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="{{ setting('center_name', config('app.name')) }}" style="max-width: 70px; max-height: 70px; width: auto; height: auto; display: block;">
                @elseif(! empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="{{ setting('center_name', config('app.name')) }}" style="max-width: 70px; max-height: 70px; width: auto; height: auto; display: block;">
                @else
                    <div style="width: 64px; height: 64px;"></div>
                @endif
            </td>
            <td style="vertical-align: middle; text-align: center;">
                <div style="font-size: 12px; font-weight: 700; color: #111827; line-height: 1.1;">{{ $title }}</div>
                @if($subtitle)
                    <div style="margin-top: 2px; font-size: 9px; color: #374151; line-height: 1.1;">{{ $subtitle }}</div>
                @endif
                <div style="margin-top: 2px; font-size: 8px; color: #6b7280; line-height: 1.1;">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
            </td>
            <td style="width: 90px;"></td>
        </tr>
    </table>
    <div style="border-top: 1px solid #d1d5db; margin-top: 6px;"></div>
@else
    <div class="flex items-center justify-between gap-2">
        <!-- Logo -->
        <div class="flex-shrink-0">
            @if(! empty($logoSrc))
                <img src="{{ $logoSrc }}" alt="{{ setting('center_name', config('app.name')) }}"
                    class="w-32 h-32 print:w-24 print:h-24 object-contain">
            @else
                <div class="w-32 h-32 print:w-24 print:h-24"></div>
            @endif
        </div>

        <!-- Información del Reporte -->
        <div class="flex-1 text-center space-y-0.5">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 print:text-gray-700 print:text-sm">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-gray-600 dark:text-gray-400 print:text-gray-700 print:text-xs">{{ $subtitle }}</p>
            @endif
            <p class="text-sm text-gray-500 dark:text-gray-500 print:text-gray-500 print:text-xs">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Espacio para balance visual -->
        <div class="flex-shrink-0 w-32 print:w-24"></div>
    </div>
@endif
