{{-- Pestaña colapsada: visible cuando el panel está cerrado --}}
<div x-show="!open"
     @click="toggleOpen()"
     class="fixed right-0 top-1/2 -translate-y-1/2 z-40 cursor-pointer select-none"
     title="Notas internas del profesional">
    <div class="flex items-center justify-center w-8 h-24 bg-gray-700 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 rounded-l-lg shadow-lg transition-colors duration-200">
        <span class="text-white text-xs font-semibold tracking-widest"
              style="writing-mode: vertical-rl; text-orientation: mixed; transform: rotate(180deg);">
            Notas
        </span>
    </div>
</div>

{{-- Panel expandido --}}
<div x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-x-full opacity-0"
     x-transition:enter-end="translate-x-0 opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-x-0 opacity-100"
     x-transition:leave-end="translate-x-full opacity-0"
     class="fixed right-0 top-0 h-screen w-72 z-40 flex flex-col bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700"
     style="box-shadow: -4px 0 15px rgba(0,0,0,0.15);">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 shrink-0">
        <div class="flex items-center gap-2 min-w-0">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"
                x-text="'Notas · ' + professionalName"></h3>
        </div>
        <button @click="open = false"
                class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">

        {{-- Spinner --}}
        <div x-show="loading" class="flex justify-center py-6">
            <svg class="animate-spin w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
        </div>

        {{-- Estado vacío --}}
        <div x-show="!loading && notes.length === 0"
             class="flex flex-col items-center justify-center py-8 text-center">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <p class="text-sm text-gray-400 dark:text-gray-500">Sin notas aún</p>
            <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Agregá la primera nota abajo</p>
        </div>

        {{-- Lista de notas --}}
        <template x-for="note in notes" :key="note.id">
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg p-3 group">
                <p class="text-sm text-gray-800 dark:text-gray-100 whitespace-pre-wrap break-words"
                   x-text="note.content"></p>
                <div class="flex items-center justify-between mt-2">
                    <div class="text-xs text-gray-400 dark:text-gray-500 leading-tight">
                        <span x-text="note.author"></span>
                        <span class="mx-1">·</span>
                        <span x-text="formatDate(note.created_at)"></span>
                    </div>
                    <button @click="deleteNote(note.id)"
                            class="opacity-0 group-hover:opacity-100 p-1 rounded text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all"
                            title="Eliminar nota">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

    </div>

    {{-- Footer: formulario de nueva nota --}}
    <div class="shrink-0 px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <textarea x-model="newNote"
                  @keydown.ctrl.enter.prevent="createNote()"
                  rows="3"
                  maxlength="500"
                  placeholder="Escribir nota... (Ctrl+Enter para guardar)"
                  class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3 py-2 resize-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"></textarea>
        <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-gray-400" x-text="newNote.length + '/500'"></span>
            <button @click="createNote()"
                    :disabled="loading || newNote.trim().length === 0"
                    class="px-3 py-1.5 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Guardar
            </button>
        </div>
    </div>

</div>
