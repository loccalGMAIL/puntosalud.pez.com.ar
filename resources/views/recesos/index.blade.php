@extends('layouts.app')

@section('title', 'Recesos y Feriados - ' . config('app.name'))
@section('mobileTitle', 'Recesos')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="recesosPage()">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>Recesos</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Recesos y Feriados
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Gestiona los días feriados y recesos del centro médico
                </p>
            </div>
            <button type="button"
                    @click="openCreateModal()"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Feriado
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v5.721c0 .926-.492 1.784-1.285 2.246l-.686.343a1.125 1.125 0 01-1.462-.396l-.423-.618a1.125 1.125 0 01-.194-.682v-5.938a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Filtros
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Filtro por año -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Año</label>
                    <select x-model="filters.year"
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos los años</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Contador de resultados -->
                <div class="flex items-end">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <strong x-text="holidays.length"></strong>
                        <span>feriados registrados</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de feriados -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                </svg>
                Lista de Feriados
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-200/50 dark:divide-emerald-800/30">
                    <thead class="bg-emerald-50/50 dark:bg-emerald-950/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-emerald-200/30 dark:divide-emerald-800/30">
                        <!-- Estado vacío -->
                        <tr x-show="holidays.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No se encontraron feriados</p>
                                </div>
                            </td>
                        </tr>

                        <!-- Filas de feriados -->
                        <template x-for="holiday in holidays" :key="holiday.id">
                            <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-950/20 transition-colors duration-200">
                                <!-- Fecha -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(holiday.exception_date)"></div>
                                </td>

                                <!-- Descripción -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white" x-text="holiday.reason"></div>
                                </td>

                                <!-- Estado -->
                                <td class="px-6 py-4">
                                    <span :class="holiday.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'"
                                          class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                                          x-text="holiday.is_active ? 'Activo' : 'Inactivo'">
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Botón Editar -->
                                        <button @click="openEditModal(holiday)"
                                                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Editar feriado">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Botón Activar/Desactivar -->
                                        <button @click="toggleStatus(holiday)"
                                                :class="holiday.is_active ? 'text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20' : 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                                class="p-2 rounded-lg transition-colors"
                                                :title="holiday.is_active ? 'Desactivar feriado' : 'Activar feriado'">
                                            <!-- Icono Desactivar (cuando está activo) -->
                                            <svg x-show="holiday.is_active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <!-- Icono Activar (cuando está inactivo) -->
                                            <svg x-show="!holiday.is_active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>

                                        <!-- Botón Eliminar -->
                                        <button @click="deleteHoliday(holiday)"
                                                class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Eliminar feriado">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4 px-6 pb-6">
                {{ $holidays->links() }}
            </div>
        </div>
    </div>

    @include('recesos.modal')
</div>

<script>
function recesosPage() {
    return {
        // Data inicial
        holidays: @json($holidays->items()),

        // Estados del modal
        modalOpen: false,
        editingHoliday: null,
        loading: false,

        // Filtros
        filters: {
            year: '{{ request("year", "") }}'
        },

        // Formulario
        form: {
            exception_date: '',
            reason: ''
        },

        // Init
        init() {
            // Listeners
        },

        // Methods
        async applyFilters() {
            try {
                const params = new URLSearchParams();

                if (this.filters.year) {
                    params.append('year', this.filters.year);
                }

                const response = await fetch(`/recesos?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                this.holidays = data.holidays;
            } catch (error) {
                console.error('Error applying filters:', error);
            }
        },

        openCreateModal() {
            console.log('Opening create modal...');
            this.editingHoliday = null;
            this.resetForm();
            this.modalOpen = true;
            console.log('Modal state:', this.modalOpen);
        },

        openEditModal(holiday) {
            console.log('Opening edit modal for:', holiday);
            this.editingHoliday = holiday;
            this.form = {
                exception_date: this.formatDateForInput(holiday.exception_date),
                reason: holiday.reason
            };
            console.log('Form data:', this.form);
            this.modalOpen = true;
        },

        resetForm() {
            this.form = {
                exception_date: '',
                reason: ''
            };
        },

        async submitForm() {
            this.loading = true;

            try {
                const url = this.editingHoliday ?
                    `/recesos/${this.editingHoliday.id}` :
                    '/recesos';
                const method = this.editingHoliday ? 'PUT' : 'POST';

                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                if (this.editingHoliday) {
                    formData.append('_method', 'PUT');
                }
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.modalOpen = false;
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    if (response.status === 422 && result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        this.showNotification('Errores de validación:\n' + errorMessages, 'error');
                    } else {
                        this.showNotification(result.message || 'Error al guardar el feriado', 'error');
                    }
                }
            } catch (error) {
                this.showNotification('Error al guardar el feriado', 'error');
            } finally {
                this.loading = false;
            }
        },

        async deleteHoliday(holiday) {
            if (!confirm('¿Estás seguro de eliminar este feriado?')) return;

            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch(`/recesos/${holiday.id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification(result.message || 'Error al eliminar el feriado', 'error');
                }
            } catch (error) {
                this.showNotification('Error al eliminar el feriado', 'error');
            }
        },

        async toggleStatus(holiday) {
            const action = holiday.is_active ? 'desactivar' : 'activar';
            if (!confirm(`¿Estás seguro de ${action} este feriado?`)) return;

            try {
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch(`/recesos/${holiday.id}/toggle-status`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification(result.message || 'Error al cambiar el estado', 'error');
                }
            } catch (error) {
                this.showNotification('Error al cambiar el estado', 'error');
            }
        },

        async refreshData() {
            // Recargar la página para reflejar los cambios
            window.location.reload();
        },

        showNotification(message, type = 'info') {
            window.showToast(message, type);
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        formatDateForInput(dateString) {
            if (!dateString) return '';
            // Convertir fecha a formato yyyy-MM-dd para input type="date"
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
@endsection
