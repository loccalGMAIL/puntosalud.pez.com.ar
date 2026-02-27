@extends('layouts.app')

@section('title', 'Perfiles - ' . config('app.name'))

@section('content')
<div x-data="profilesApp()" x-init="init()" class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

    <!-- Breadcrumb -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">Dashboard</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">Perfiles</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Perfiles</h1>
        @can('create', App\Models\Profile::class)
        <button @click="openModal('create')"
                class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuevo Perfil
        </button>
        @endcan
    </div>

    <!-- Tabla de perfiles -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/50 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Perfil
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Módulos habilitados
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Usuarios
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($profiles as $profile)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->name }}</div>
                            @if($profile->description)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $profile->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($modules as $key => $label)
                                    @if($profile->modules->contains('module', $key))
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        {{ $label }}
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $profile->users_count }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @can('update', $profile)
                            <button @click="openModal('edit', {{ $profile->id }}, '{{ addslashes($profile->name) }}', '{{ addslashes($profile->description ?? '') }}', {{ $profile->modules->pluck('module')->toJson() }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Editar
                            </button>
                            @endcan
                            @can('delete', $profile)
                            <button @click="deleteProfile({{ $profile->id }}, '{{ addslashes($profile->name) }}', {{ $profile->users_count }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                Eliminar
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No hay perfiles registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="showModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.outside="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-gray-800 rounded-xl max-w-lg w-full shadow-xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                        x-text="isEditing ? 'Editar Perfil' : 'Nuevo Perfil'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm()">
                    <div class="space-y-5">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Nombre del perfil
                            </label>
                            <input type="text" x-model="form.name" required
                                   :class="hasError('name') ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600'"
                                   class="w-full px-3 py-2 border dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <p x-show="hasError('name')" x-text="formErrors.name?.[0]"
                               class="text-xs text-red-500 mt-1"></p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Descripción <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" x-model="form.description"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <!-- Módulos -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Módulos habilitados
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($modules as $key => $label)
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                       :class="form.modules.includes('{{ $key }}') ? 'border-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-700' : ''">
                                    <input type="checkbox" value="{{ $key }}"
                                           x-model="form.modules"
                                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" :disabled="loading"
                                class="flex-1 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                            <span x-text="loading ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear Perfil')"></span>
                        </button>
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function profilesApp() {
    return {
        showModal: false,
        isEditing: false,
        loading: false,
        currentId: null,
        form: {
            name: '',
            description: '',
            modules: [],
        },
        formErrors: {},

        init() {},

        hasError(field) {
            return !!this.formErrors[field];
        },

        clearErrors() {
            this.formErrors = {};
        },

        openModal(action, id = null, name = '', description = '', modules = []) {
            this.clearErrors();
            this.isEditing = action === 'edit';
            this.currentId = id;
            this.form = {
                name: name,
                description: description,
                modules: Array.isArray(modules) ? modules : [],
            };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.clearErrors();
        },

        async submitForm() {
            this.loading = true;
            this.clearErrors();

            const url = this.isEditing
                ? `/profiles/${this.currentId}`
                : '/profiles';

            const body = new FormData();
            body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if (this.isEditing) body.append('_method', 'PUT');
            body.append('name', this.form.name);
            body.append('description', this.form.description);
            this.form.modules.forEach(m => body.append('modules[]', m));

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.closeModal();
                    window.showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else if (data.errors) {
                    this.formErrors = data.errors;
                } else {
                    window.showToast(data.message || 'Error al guardar', 'error');
                }
            } catch (e) {
                window.showToast('Error al procesar la solicitud', 'error');
            } finally {
                this.loading = false;
            }
        },

        async deleteProfile(id, name, usersCount) {
            if (usersCount > 0) {
                window.showToast(`No se puede eliminar: el perfil tiene ${usersCount} usuario(s) asignado(s)`, 'error');
                return;
            }

            if (!confirm(`¿Eliminar el perfil "${name}"? Esta acción no se puede deshacer.`)) return;

            try {
                const response = await fetch(`/profiles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    window.showToast(data.error || 'Error al eliminar', 'error');
                }
            } catch (e) {
                window.showToast('Error al procesar la solicitud', 'error');
            }
        },
    };
}
</script>
@endpush
@endsection
