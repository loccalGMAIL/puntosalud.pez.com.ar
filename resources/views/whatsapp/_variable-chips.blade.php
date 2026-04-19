<div class="mt-2 flex flex-wrap gap-2 items-center">
    <span class="text-xs text-gray-400 dark:text-gray-500">Variables:</span>
    @foreach (['nombre', 'fecha', 'hora', 'profesional', 'especialidad'] as $var)
    @php $tag = '{{' . $var . '}}'; @endphp
    <button type="button"
            @click="{{ $handler }}"
            x-data="{ variable: '{{ $tag }}' }"
            class="inline-flex items-center px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs rounded font-mono hover:bg-emerald-100 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors cursor-pointer">
        {{ $tag }}
    </button>
    @endforeach
</div>
