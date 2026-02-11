{{-- Sistema de Toast Notifications Global --}}
<div x-data x-cloak>
    <div class="fixed bottom-4 right-4 z-[60] flex flex-col-reverse gap-2 max-w-sm w-full pointer-events-none">
        <template x-for="toast in $store.toasts.list" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="pointer-events-auto w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-start gap-3 p-4">
                    {{-- Icono por tipo --}}
                    <div class="flex-shrink-0 mt-0.5">
                        <template x-if="toast.type === 'success'">
                            <div class="w-6 h-6 text-green-500 dark:text-green-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <div class="w-6 h-6 text-red-500 dark:text-red-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <div class="w-6 h-6 text-amber-500 dark:text-amber-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <div class="w-6 h-6 text-blue-500 dark:text-blue-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Mensaje --}}
                    <p class="flex-1 text-sm text-gray-700 dark:text-gray-200" x-text="toast.message"></p>

                    {{-- Botón cerrar --}}
                    <button @click="$store.toasts.dismiss(toast.id)"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('toasts', {
        list: [],
        _counter: 0,

        _add(type, message, duration) {
            const id = ++this._counter;
            this.list.push({ id, type, message, visible: true });
            if (duration > 0) {
                setTimeout(() => this.dismiss(id), duration);
            }
        },

        dismiss(id) {
            const toast = this.list.find(t => t.id === id);
            if (toast) {
                toast.visible = false;
                setTimeout(() => {
                    this.list = this.list.filter(t => t.id !== id);
                }, 300);
            }
        },

        success(message) { this._add('success', message, 4000); },
        error(message)   { this._add('error', message, 6000); },
        warning(message) { this._add('warning', message, 5000); },
        info(message)    { this._add('info', message, 4000); }
    });
});

// Función global para contextos fuera de Alpine
window.showToast = function(message, type = 'info') {
    if (window.Alpine && Alpine.store('toasts')) {
        const store = Alpine.store('toasts');
        if (store[type]) {
            store[type](message);
        } else {
            store.info(message);
        }
    }
};
</script>
