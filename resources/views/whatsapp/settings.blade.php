@extends('layouts.app')

@section('title', 'WhatsApp - Configuración - ' . config('app.name'))
@section('mobileTitle', 'WhatsApp Config')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="whatsappSettings()">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('whatsapp.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">WhatsApp</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Configuración</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Configuración de WhatsApp
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Configurá la conexión con Evolution API y el mensaje de recordatorio
            </p>
        </div>
    </div>

    @if (session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-5 py-4 text-emerald-700 dark:text-emerald-300 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-5 py-4">
        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('whatsapp.settings.save') }}" class="space-y-6">
        @csrf

        <!-- Recordatorio -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Configuración del recordatorio</h2>
            <div class="space-y-5">

                <!-- Anticipación -->
                <div>
                    <label for="hours_before" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Enviar el recordatorio con cuánta anticipación
                    </label>
                    <select id="hours_before" name="hours_before"
                            class="w-full md:w-64 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors">
                        @foreach ([1 => '1 hora antes', 2 => '2 horas antes', 4 => '4 horas antes', 12 => '12 horas antes', 24 => '24 horas antes (1 día)', 48 => '48 horas antes (2 días)'] as $val => $label)
                        <option value="{{ $val }}" {{ old('hours_before', $current['hours_before']) == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plantilla -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="template" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mensaje de recordatorio
                        </label>
                        <span class="text-xs text-gray-400" x-text="template.length + '/1000 caracteres'"></span>
                    </div>
                    <textarea id="template" name="template" rows="4" maxlength="1000"
                              x-model="template"
                              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors resize-none">{{ old('template', $current['template']) }}</textarea>

                    <!-- Variables disponibles -->
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Variables disponibles:</span>
                        @foreach (['nombre', 'fecha', 'hora', 'profesional', 'especialidad'] as $var)
                        @php $varTag = '{{' . $var . '}}'; @endphp
                        <button type="button"
                                @click="insertVariable('{{ $varTag }}')"
                                class="inline-flex items-center px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded font-mono hover:bg-emerald-100 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors cursor-pointer">
                            {{ $varTag }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Preview del mensaje -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Vista previa del mensaje</p>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-start gap-2">
                            <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">PuntoSalud</p>
                                <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap" x-text="previewMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón guardar -->
        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Guardar configuración
            </button>
        </div>
    </form>
</div>

<script>
function whatsappSettings() {
    return {
        template: @js(old('template', $current['template'])),

        get previewMessage() {
            const now = new Date();
            const fecha = now.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const hora  = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
            return this.template
                .replace(/\{\{nombre\}\}/g, 'María López')
                .replace(/\{\{fecha\}\}/g, fecha)
                .replace(/\{\{hora\}\}/g, hora)
                .replace(/\{\{profesional\}\}/g, 'Dr. Juan García')
                .replace(/\{\{especialidad\}\}/g, 'Clínica Médica');
        },

        insertVariable(variable) {
            const textarea = document.getElementById('template');
            const start    = textarea.selectionStart;
            const end      = textarea.selectionEnd;
            this.template  = this.template.substring(0, start) + variable + this.template.substring(end);
            this.$nextTick(() => {
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                textarea.focus();
            });
        }
    };
}
</script>
@endsection
