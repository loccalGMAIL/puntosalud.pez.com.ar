@extends('layouts.app')

@section('title', 'WhatsApp - Plantillas - ' . config('app.name'))
@section('mobileTitle', 'WA Plantillas')

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
                <span>Plantillas</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Plantillas de mensajes
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Configurá el texto de cada mensaje que se envía automáticamente por WhatsApp
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

    <form method="POST" action="{{ route('whatsapp.settings.save') }}" class="space-y-3">
        @csrf

        {{-- ── ACORDEÓN 1: Recordatorio previo al turno ──────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

            {{-- Cabecera --}}
            <button type="button" @click="toggle('reminder')"
                    class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Recordatorio previo al turno</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Se envía automáticamente con anticipación configurable</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open === 'reminder' }"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            {{-- Preview colapsado --}}
            <div x-show="open !== 'reminder'" class="px-5 pb-4 border-t border-gray-100 dark:border-gray-700/50">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-2 mt-3 uppercase tracking-wide font-medium">Vista previa</p>
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm px-4 py-3 max-w-sm">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="preview('reminder')"></p>
                </div>
            </div>

            {{-- Contenido expandido --}}
            <div x-show="open === 'reminder'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="px-5 pb-5 border-t border-gray-200 dark:border-gray-700 space-y-5 pt-5">

                {{-- Anticipación --}}
                <div>
                    <label for="hours_before" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Enviar con cuánta anticipación
                    </label>
                    <select id="hours_before" name="hours_before"
                            class="w-full md:w-72 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors">
                        @foreach ([1 => '1 hora antes', 2 => '2 horas antes', 4 => '4 horas antes', 12 => '12 horas antes', 24 => '24 horas antes (1 día)', 48 => '48 horas antes (2 días)'] as $val => $label)
                        <option value="{{ $val }}" {{ old('hours_before', $current['hours_before']) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Textarea --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="template" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Texto del mensaje</label>
                        <span class="text-xs text-gray-400" x-text="templates.reminder.length + '/1000'"></span>
                    </div>
                    <textarea id="template" name="template" rows="5" maxlength="1000"
                              x-model="templates.reminder"
                              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors resize-none">{{ old('template', $current['template']) }}</textarea>
                    @include('whatsapp._variable-chips', ['handler' => "insertVar('reminder', variable)"])
                </div>

                {{-- Preview expandido --}}
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm px-4 py-3">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="preview('reminder')"></p>
                </div>
            </div>
        </div>

        {{-- ── ACORDEÓN 2: Confirmación de turno ─────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

            <button type="button" @click="toggle('creation')"
                    class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Confirmación de turno</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Se envía al paciente en el momento en que se registra el turno</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open === 'creation' }"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="open !== 'creation'" class="px-5 pb-4 border-t border-gray-100 dark:border-gray-700/50">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-2 mt-3 uppercase tracking-wide font-medium">Vista previa</p>
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm px-4 py-3 max-w-sm">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="preview('creation')"></p>
                </div>
            </div>

            <div x-show="open === 'creation'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="px-5 pb-5 border-t border-gray-200 dark:border-gray-700 space-y-5 pt-5">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="template_on_create" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Texto del mensaje</label>
                        <span class="text-xs text-gray-400" x-text="templates.creation.length + '/1000'"></span>
                    </div>
                    <textarea id="template_on_create" name="template_on_create" rows="5" maxlength="1000"
                              x-model="templates.creation"
                              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors resize-none">{{ old('template_on_create', $current['template_on_create']) }}</textarea>
                    @include('whatsapp._variable-chips', ['handler' => "insertVar('creation', variable)"])
                </div>
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm px-4 py-3">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="preview('creation')"></p>
                </div>
            </div>
        </div>

        {{-- ── ACORDEÓN 3: Aviso de cancelación ──────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

            <button type="button" @click="toggle('cancellation')"
                    class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Aviso de cancelación</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Se envía al paciente cuando su turno es cancelado</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open === 'cancellation' }"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="open !== 'cancellation'" class="px-5 pb-4 border-t border-gray-100 dark:border-gray-700/50">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-2 mt-3 uppercase tracking-wide font-medium">Vista previa</p>
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm px-4 py-3 max-w-sm">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="preview('cancellation')"></p>
                </div>
            </div>

            <div x-show="open === 'cancellation'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="px-5 pb-5 border-t border-gray-200 dark:border-gray-700 space-y-5 pt-5">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="template_on_cancel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Texto del mensaje</label>
                        <span class="text-xs text-gray-400" x-text="templates.cancellation.length + '/1000'"></span>
                    </div>
                    <textarea id="template_on_cancel" name="template_on_cancel" rows="5" maxlength="1000"
                              x-model="templates.cancellation"
                              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-colors resize-none">{{ old('template_on_cancel', $current['template_on_cancel']) }}</textarea>
                    @include('whatsapp._variable-chips', ['handler' => "insertVar('cancellation', variable)"])
                </div>
                <div class="bg-[#e9fde9] dark:bg-emerald-900/20 rounded-2xl rounded-tl-sm px-4 py-3">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="preview('cancellation')"></p>
                </div>
            </div>
        </div>

        {{-- Botón guardar --}}
        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Guardar plantillas
            </button>
        </div>
    </form>
</div>

<script>
function whatsappSettings() {
    const SAMPLE = {
        nombre:       'María López',
        profesional:  'Dr. Juan García',
        especialidad: 'Clínica Médica',
        fecha:        new Date().toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' }),
        hora:         new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', hour12: false }),
    };

    return {
        open: null,   // cuál acordeón está abierto (null = todos cerrados)

        templates: {
            reminder:     @js(old('template',           $current['template'])),
            creation:     @js(old('template_on_create', $current['template_on_create'])),
            cancellation: @js(old('template_on_cancel', $current['template_on_cancel'])),
        },

        toggle(key) {
            this.open = this.open === key ? null : key;
        },

        preview(key) {
            const tpl = this.templates[key] || '';
            return tpl
                .replace(/\{\{nombre\}\}/g,       SAMPLE.nombre)
                .replace(/\{\{profesional\}\}/g,  SAMPLE.profesional)
                .replace(/\{\{especialidad\}\}/g, SAMPLE.especialidad)
                .replace(/\{\{fecha\}\}/g,        SAMPLE.fecha)
                .replace(/\{\{hora\}\}/g,         SAMPLE.hora);
        },

        insertVar(key, variable) {
            const ids = { reminder: 'template', creation: 'template_on_create', cancellation: 'template_on_cancel' };
            const textarea = document.getElementById(ids[key]);
            if (! textarea) return;
            const start = textarea.selectionStart;
            const end   = textarea.selectionEnd;
            this.templates[key] = this.templates[key].substring(0, start) + variable + this.templates[key].substring(end);
            this.$nextTick(() => {
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                textarea.focus();
            });
        }
    };
}
</script>
@endsection
