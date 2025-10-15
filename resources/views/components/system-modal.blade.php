{{-- Modal del Sistema Reutilizable --}}
<div id="systemModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="systemModalContent">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div id="systemModalIcon" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                    <!-- El icono se insertará dinámicamente -->
                </div>
                <h3 id="systemModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">
                    Título del Modal
                </h3>
            </div>
            <button type="button" onclick="SystemModal.close()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <p id="systemModalMessage" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Mensaje del modal
            </p>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button"
                    onclick="SystemModal.close()"
                    id="systemModalCancelBtn"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Cancelar
            </button>
            <button type="button"
                    id="systemModalConfirmBtn"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                Aceptar
            </button>
        </div>
    </div>
</div>

<script>
// Sistema de Modal Global
window.SystemModal = {
    currentCallback: null,

    // Configuraciones de tipos de modal
    types: {
        success: {
            iconBg: 'bg-green-100 dark:bg-green-900/30',
            iconColor: 'text-green-600 dark:text-green-400',
            btnColor: 'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800',
            icon: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                   </svg>`
        },
        error: {
            iconBg: 'bg-red-100 dark:bg-red-900/30',
            iconColor: 'text-red-600 dark:text-red-400',
            btnColor: 'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800',
            icon: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                   </svg>`
        },
        warning: {
            iconBg: 'bg-amber-100 dark:bg-amber-900/30',
            iconColor: 'text-amber-600 dark:text-amber-400',
            btnColor: 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-800',
            icon: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                   </svg>`
        },
        confirm: {
            iconBg: 'bg-blue-100 dark:bg-blue-900/30',
            iconColor: 'text-blue-600 dark:text-blue-400',
            btnColor: 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800',
            icon: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                   </svg>`
        }
    },

    // Mostrar modal de información/éxito/error
    show(type, title, message, confirmText = 'Aceptar', showCancel = false) {
        return new Promise((resolve) => {
            const modal = document.getElementById('systemModal');
            const content = document.getElementById('systemModalContent');
            const titleEl = document.getElementById('systemModalTitle');
            const messageEl = document.getElementById('systemModalMessage');
            const iconEl = document.getElementById('systemModalIcon');
            const confirmBtn = document.getElementById('systemModalConfirmBtn');
            const cancelBtn = document.getElementById('systemModalCancelBtn');

            const config = this.types[type] || this.types.confirm;

            // Configurar contenido
            titleEl.textContent = title;
            messageEl.textContent = message;
            confirmBtn.textContent = confirmText;

            // Configurar estilos del icono
            iconEl.className = `flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3 ${config.iconBg} ${config.iconColor}`;
            iconEl.innerHTML = config.icon;

            // Configurar botón
            confirmBtn.className = `px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors ${config.btnColor}`;

            // Mostrar/ocultar botón cancelar
            cancelBtn.style.display = showCancel ? 'block' : 'none';

            // Configurar callback
            this.currentCallback = resolve;

            // Mostrar modal con animación
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            // Event listener para el botón confirmar
            confirmBtn.onclick = () => {
                this.close();
                resolve(true);
            };
        });
    },

    // Mostrar modal de confirmación
    confirm(title, message, confirmText = 'Confirmar', cancelText = 'Cancelar') {
        return new Promise((resolve) => {
            const modal = document.getElementById('systemModal');
            const content = document.getElementById('systemModalContent');
            const titleEl = document.getElementById('systemModalTitle');
            const messageEl = document.getElementById('systemModalMessage');
            const iconEl = document.getElementById('systemModalIcon');
            const confirmBtn = document.getElementById('systemModalConfirmBtn');
            const cancelBtn = document.getElementById('systemModalCancelBtn');

            const config = this.types.confirm;

            // Configurar contenido
            titleEl.textContent = title;
            messageEl.innerHTML = message; // Permitir HTML para saltos de línea
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;

            // Configurar estilos del icono
            iconEl.className = `flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3 ${config.iconBg} ${config.iconColor}`;
            iconEl.innerHTML = config.icon;

            // Configurar botón
            confirmBtn.className = `px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors ${config.btnColor}`;

            // Mostrar botón cancelar
            cancelBtn.style.display = 'block';

            // Mostrar modal con animación
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            // Event listeners
            confirmBtn.onclick = () => {
                this.close();
                resolve(true);
            };

            cancelBtn.onclick = () => {
                this.close();
                resolve(false);
            };
        });
    },

    // Cerrar modal
    close() {
        const modal = document.getElementById('systemModal');
        const content = document.getElementById('systemModalContent');

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
};

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        SystemModal.close();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('systemModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        SystemModal.close();
    }
});
</script>