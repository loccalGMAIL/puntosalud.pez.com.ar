@extends('layouts.app')

@section('title', 'Profesionales - ' . config('app.name'))
@section('mobileTitle', 'Profesionales')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="professionalsPage()">
    
    <!-- Header con estad�sticas -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>Profesionales</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Profesionales
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Gestiona los profesionales del centro médico
                </p>
            </div>
            <button @click="openCreateModal()" 
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <!-- Plus Icon -->
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Profesional
            </button>
        </div>

        <!-- Cards de estad�sticas -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total">{{ $stats['total'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">profesionales registrados</p>
                    </div>
                </div>
            </div>

            <!-- Activos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Activos</dt>
                        <dd class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" x-text="stats.active">{{ $stats['active'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">profesionales activos</p>
                    </div>
                </div>
            </div>

            <!-- Inactivos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.5a8.25 8.25 0 0116.5 0v.5H4v-.5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactivos</dt>
                        <dd class="text-2xl font-bold text-red-600 dark:text-red-400" x-text="stats.inactive">{{ $stats['inactive'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">profesionales inactivos</p>
                    </div>
                </div>
            </div>

            <!-- Especialidades -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Especialidades</dt>
                        <dd class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['specialties_count'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">especialidades médicas</p>
                    </div>
                </div>
            </div>
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
                    Filtros y Búsqueda
                </h3>
                <button x-show="hasActiveFilters" 
                        @click="clearFilters()"
                        class="text-sm text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium">
                    Limpiar filtros
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Búsqueda -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <input x-model="filters.search"
                           type="text"
                           placeholder="Buscar por nombre, DNI o email..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Especialidad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Especialidad</label>
                    <select x-model="filters.specialty"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todas las especialidades</option>
                        @foreach($specialties as $specialty)
                            <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select x-model="filters.status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>

                <!-- Contador -->
                <div class="flex items-end">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <strong x-text="filteredProfessionals.length"></strong>
                        <span x-show="hasActiveFilters">resultados encontrados</span>
                        <span x-show="!hasActiveFilters">profesionales en total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de profesionales -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-6">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Lista de Profesionales
            </h3>
            
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                <div x-show="filteredProfessionals.length === 0" class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No se encontraron profesionales</p>
                </div>
                <template x-for="professional in filteredProfessionals" :key="'mobile-'+professional.id">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white" x-text="professional.first_name + ' ' + professional.last_name"></span>
                            <span :class="professional.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'"
                                  class="text-xs font-medium rounded-full px-2 py-0.5"
                                  x-text="professional.is_active ? 'Activo' : 'Inactivo'"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <div><span class="inline-flex px-1.5 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded text-xs" x-text="professional.specialty.name"></span></div>
                            <div><span class="font-medium">Com:</span> <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="professional.commission_percentage + '%'"></span></div>
                            <div><span class="font-medium">Mat:</span> <span x-text="professional.license_number || '-'" class="font-mono"></span></div>
                            <div><span class="font-medium">Tel:</span> <span x-text="professional.phone || '-'"></span></div>
                        </div>
                        <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <a :href="`/professionals/${professional.id}/schedules`"
                               class="p-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg" title="Horarios">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </a>
                            <button @click="openEditModal(professional)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg" title="Editar">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>
                            <button @click="toggleStatus(professional)"
                                    :class="professional.is_active ? 'text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20' : 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                    class="p-2 rounded-lg"
                                    :title="professional.is_active ? 'Desactivar' : 'Activar'">
                                <svg x-show="professional.is_active" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.5a8.25 8.25 0 0116.5 0v.5H4v-.5z" />
                                </svg>
                                <svg x-show="!professional.is_active" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block rounded-md border border-emerald-200/50 dark:border-emerald-800/30 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-200/50 dark:divide-emerald-800/30">
                        <thead class="bg-emerald-50/50 dark:bg-emerald-950/20">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Profesional</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Matrícula</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Especialidad</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Tel.</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Com.</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Estado</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-tight">Acc.</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-emerald-200/30 dark:divide-emerald-800/30">
                            <!-- Estado vac�o -->
                            <tr x-show="filteredProfessionals.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                        <p class="text-gray-600 dark:text-gray-400">No se encontraron profesionales</p>
                                        <button @click="clearFilters()" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                            Limpiar filtros
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Filas de profesionales -->
                            <template x-for="professional in filteredProfessionals" :key="professional.id">
                                <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-950/20 transition-colors duration-200">
                                    <!-- Profesional -->
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-gray-900 dark:text-white" x-text="professional.first_name + ' ' + professional.last_name"></div>
                                    </td>

                                    <!-- Matrícula -->
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <span class="text-xs font-mono text-gray-900 dark:text-white" x-text="professional.license_number || '-'"></span>
                                    </td>

                                    <!-- Especialidad -->
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <span class="inline-flex px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded" x-text="professional.specialty.name"></span>
                                    </td>

                                    <!-- Teléfono -->
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <span class="text-xs text-gray-900 dark:text-white" x-text="professional.phone || '-'"></span>
                                    </td>

                                    <!-- Comisi�n -->
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400" x-text="professional.commission_percentage + '%'"></span>
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <span :class="professional.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'"
                                              class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded"
                                              x-text="professional.is_active ? 'Activo' : 'Inactivo'">
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-2 py-2 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1">
                                            <!-- Botón Horarios -->
                                            <a :href="`/professionals/${professional.id}/schedules`"
                                               class="p-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded transition-colors"
                                               title="Configurar horarios">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </a>

                                            <!-- Botón Editar -->
                                            <button @click="openEditModal(professional)"
                                                    class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                                    title="Editar profesional">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <!-- Botón Activar/Desactivar -->
                                            <button @click="toggleStatus(professional)"
                                                    :class="professional.is_active ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' : 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                                    class="p-1 rounded transition-colors"
                                                    :title="professional.is_active ? 'Desactivar profesional' : 'Activar profesional'">
                                                <!-- Icono Desactivar (cuando está activo) -->
                                                <svg x-show="professional.is_active" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.5a8.25 8.25 0 0116.5 0v.5H4v-.5z" />
                                                </svg>
                                                <!-- Icono Activar (cuando está inactivo) -->
                                                <svg x-show="!professional.is_active" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                    {{ $professionals->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('professionals.modal')
</div>

<script>
function professionalsPage() {
    return {
        // Data inicial
        professionals: @json($professionals->items()),
        specialties: @json($specialties),
        stats: @json($stats),
        
        // Estados del modal
        modalOpen: false,
        editingProfessional: null,
        loading: false,
        formErrors: {},
        
        // Estados del modal de especialidades
        specialtyModalOpen: false,
        specialtyLoading: false,
        
        // Filtros
        filters: {
            search: '',
            specialty: 'all',
            status: 'all'
        },

        // Debounce timer
        searchTimeout: null,
        
        // Formulario
        form: {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            birthday: '',
            dni: '',
            license_number: '',
            specialty_id: '',
            commission_percentage: '',
            receives_transfers_directly: false,
            notes: '',
            is_active: true
        },
        
        // Formulario de especialidad
        specialtyForm: {
            name: '',
            description: ''
        },
        
        // Computed
        get filteredProfessionals() {
            // Simplemente retornamos los profesionales ya que el filtrado se hace en backend
            return this.professionals;
        },
        
        get hasActiveFilters() {
            return this.filters.search !== '' ||
                   this.filters.specialty !== 'all' ||
                   this.filters.status !== 'all';
        },

        // Init
        init() {
            // Watch para búsqueda con debounce
            this.$watch('filters.search', () => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.applyFilters();
                }, 500); // Espera 500ms después de que el usuario deje de escribir
            });

            // Watch para filtros sin debounce
            this.$watch('filters.specialty', () => {
                this.applyFilters();
            });

            this.$watch('filters.status', () => {
                this.applyFilters();
            });
        },

        // Methods
        async applyFilters() {
            try {
                const params = new URLSearchParams();

                if (this.filters.search) {
                    params.append('search', this.filters.search);
                }
                if (this.filters.specialty !== 'all') {
                    params.append('specialty', this.filters.specialty);
                }
                if (this.filters.status !== 'all') {
                    params.append('status', this.filters.status);
                }

                const response = await fetch(`/professionals?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                this.professionals = data.professionals;
                this.stats = data.stats;
            } catch (error) {
                console.error('Error applying filters:', error);
            }
        },

        openCreateModal() {
            this.editingProfessional = null;
            this.resetForm();
            this.clearAllErrors();
            this.modalOpen = true;
        },

        openEditModal(professional) {
            this.editingProfessional = professional;
            this.clearAllErrors();
            this.form = {
                first_name: professional.first_name,
                last_name: professional.last_name,
                email: professional.email || '',
                phone: professional.phone || '',
                birthday: professional.birthday || '',
                dni: professional.dni,
                license_number: professional.license_number || '',
                specialty_id: professional.specialty.id.toString(),
                commission_percentage: professional.commission_percentage,
                receives_transfers_directly: professional.receives_transfers_directly || false,
                notes: professional.notes || '',
                is_active: professional.is_active.toString()
            };
            this.modalOpen = true;
        },
        
        resetForm() {
            this.form = {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                birthday: '',
                dni: '',
                license_number: '',
                specialty_id: '',
                commission_percentage: '',
                receives_transfers_directly: false,
                notes: '',
                is_active: 'true'
            };
        },
        
        async submitForm() {
            this.loading = true;
            
            try {
                const url = this.editingProfessional ? 
                    `/professionals/${this.editingProfessional.id}` : 
                    '/professionals';
                const method = this.editingProfessional ? 'PUT' : 'POST';
                
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== '' || key === 'is_active' || key === 'receives_transfers_directly') {
                        // Convertir is_active a 1 o 0 para Laravel
                        if (key === 'is_active') {
                            formData.append(key, this.form[key] === 'true' ? '1' : '0');
                        } else if (key === 'receives_transfers_directly') {
                            // Convertir receives_transfers_directly a 1 o 0 para Laravel
                            formData.append(key, this.form[key] ? '1' : '0');
                        } else {
                            formData.append(key, this.form[key]);
                        }
                    }
                });
                
                if (this.editingProfessional) {
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
                        this.setErrors(result.errors);
                        this.showNotification('Por favor corregí los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al guardar el profesional', 'error');
                    }
                }
            } catch (error) {
                this.showNotification('Error al guardar el profesional', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async toggleStatus(professional) {
            const action = professional.is_active ? 'desactivar' : 'activar';
            if (!confirm(`�Est�s seguro de ${action} este profesional?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('is_active', !professional.is_active);
                formData.append('_method', 'PUT');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch(`/professionals/${professional.id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.refreshData();
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification('Error al actualizar el estado', 'error');
                }
            } catch (error) {
                this.showNotification('Error al actualizar el estado', 'error');
            }
        },
        
        async refreshData() {
            // Recargar la página para reflejar los cambios
            window.location.reload();
        },

        clearFilters() {
            this.filters = {
                search: '',
                specialty: 'all',
                status: 'all'
            };
            // Los watchers se encargarán de aplicar los filtros automáticamente
        },
        
        clearError(field) { delete this.formErrors[field]; },
        clearAllErrors() { this.formErrors = {}; },
        setErrors(errors) {
            this.formErrors = {};
            Object.keys(errors).forEach(key => {
                this.formErrors[key] = errors[key][0];
            });
        },
        hasError(field) { return !!this.formErrors[field]; },

        showNotification(message, type = 'info') {
            window.showToast(message, type);
        },

        calculateAge(birthDate) {
            if (!birthDate) return '';
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age;
        },

        getMaxDate() {
            return new Date().toISOString().split('T')[0];
        },
        
        // Funciones para especialidades
        openSpecialtyModal() {
            this.resetSpecialtyForm();
            this.specialtyModalOpen = true;
        },
        
        resetSpecialtyForm() {
            this.specialtyForm = {
                name: '',
                description: ''
            };
        },
        
        async submitSpecialtyForm() {
            this.specialtyLoading = true;
            
            try {
                const formData = new FormData();
                formData.append('name', this.specialtyForm.name);
                formData.append('description', this.specialtyForm.description);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch('/specialties', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Agregar la nueva especialidad a la lista
                    this.specialties.push(result.specialty);
                    
                    // Seleccionar la nueva especialidad automáticamente
                    this.form.specialty_id = result.specialty.id.toString();
                    
                    this.specialtyModalOpen = false;
                    this.showNotification(result.message, 'success');
                } else {
                    this.showNotification('Error al crear la especialidad', 'error');
                }
            } catch (error) {
                this.showNotification('Error al crear la especialidad', 'error');
            } finally {
                this.specialtyLoading = false;
            }
        },
        
        async refreshSpecialties() {
            try {
                const response = await fetch('/specialties', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                this.specialties = data.specialties || data;
            } catch (error) {
                console.error('Error refreshing specialties:', error);
            }
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