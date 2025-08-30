@extends('layouts.app')

@section('title', 'Profesionales - ' . config('app.name'))

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="professionalsPage()">
    
    <!-- Header con estad�sticas -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
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
                <!-- B�squeda -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <input x-model="filters.search" 
                           @input="filterProfessionals()"
                           type="text" 
                           placeholder="Buscar por nombre, DNI o email..." 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Especialidad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Especialidad</label>
                    <select x-model="filters.specialty" 
                            @change="filterProfessionals()"
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
                            @change="filterProfessionals()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>

                <!-- Contador -->
                <div class="flex items-end">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <strong x-text="filteredProfessionals.length">{{ $professionals->count() }}</strong> de 
                        <strong x-text="stats.total">{{ $stats['total'] }}</strong> profesionales
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
            
            <div class="rounded-md border border-emerald-200/50 dark:border-emerald-800/30 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-200/50 dark:divide-emerald-800/30">
                        <thead class="bg-emerald-50/50 dark:bg-emerald-950/20">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Profesional</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Especialidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Contacto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">DNI</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Comisión</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
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
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="professional.first_name + ' ' + professional.last_name"></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400" x-text="'ID: ' + professional.id"></div>
                                        </div>
                                    </td>
                                    
                                    <!-- Especialidad -->
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-full" x-text="professional.specialty.name"></span>
                                    </td>
                                    
                                    <!-- Contacto -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white" x-text="professional.email || 'Sin mail'"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="professional.phone || 'Sin teléfono'"></div>
                                    </td>
                                    
                                    <!-- DNI -->
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-mono text-gray-900 dark:text-white" x-text="professional.dni"></span>
                                    </td>
                                    
                                    <!-- Comisi�n -->
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400" x-text="professional.commission_percentage + '%'"></span>
                                    </td>
                                    
                                    <!-- Estado -->
                                    <td class="px-6 py-4">
                                        <span :class="professional.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'" 
                                              class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                                              x-text="professional.is_active ? 'Activo' : 'Inactivo'">
                                        </span>
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Botón Horarios -->
                                            <a :href="`/professionals/${professional.id}/schedules`"
                                               class="p-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors"
                                               title="Configurar horarios">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </a>

                                            <!-- Botón Editar -->
                                            <button @click="openEditModal(professional)" 
                                                    class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                    title="Editar profesional">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <!-- Botón Activar/Desactivar -->
                                            <button @click="toggleStatus(professional)" 
                                                    :class="professional.is_active ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' : 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                                    class="p-2 rounded-lg transition-colors"
                                                    :title="professional.is_active ? 'Desactivar profesional' : 'Activar profesional'">
                                                <!-- Icono Desactivar (cuando está activo) -->
                                                <svg x-show="professional.is_active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.5a8.25 8.25 0 0116.5 0v.5H4v-.5z" />
                                                </svg>
                                                <!-- Icono Activar (cuando está inactivo) -->
                                                <svg x-show="!professional.is_active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
            </div>
        </div>
    </div>

    @include('professionals.modal') 
</div>

<script>
function professionalsPage() {
    return {
        // Data inicial
        professionals: @json($professionals),
        specialties: @json($specialties),
        stats: @json($stats),
        
        // Estados del modal
        modalOpen: false,
        editingProfessional: null,
        loading: false,
        
        // Estados del modal de especialidades
        specialtyModalOpen: false,
        specialtyLoading: false,
        
        // Filtros
        filters: {
            search: '',
            specialty: 'all',
            status: 'all'
        },
        
        // Formulario
        form: {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            dni: '',
            specialty_id: '',
            commission_percentage: '',
            is_active: true
        },
        
        // Formulario de especialidad
        specialtyForm: {
            name: '',
            description: ''
        },
        
        // Computed
        get filteredProfessionals() {
            return this.professionals.filter(professional => {
                const searchMatch = this.filters.search === '' || 
                    (professional.first_name + ' ' + professional.last_name).toLowerCase().includes(this.filters.search.toLowerCase()) ||
                    professional.email.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                    professional.dni.toLowerCase().includes(this.filters.search.toLowerCase());

                const specialtyMatch = this.filters.specialty === 'all' || 
                    professional.specialty.id.toString() === this.filters.specialty;

                const statusMatch = this.filters.status === 'all' || 
                    (this.filters.status === 'active' && professional.is_active) ||
                    (this.filters.status === 'inactive' && !professional.is_active);

                return searchMatch && specialtyMatch && statusMatch;
            });
        },
        
        get hasActiveFilters() {
            return this.filters.search !== '' || 
                   this.filters.specialty !== 'all' || 
                   this.filters.status !== 'all';
        },
        
        // Methods
        openCreateModal() {
            this.editingProfessional = null;
            this.resetForm();
            this.modalOpen = true;
        },
        
        openEditModal(professional) {
            this.editingProfessional = professional;
            this.form = {
                first_name: professional.first_name,
                last_name: professional.last_name,
                email: professional.email || '',
                phone: professional.phone || '',
                dni: professional.dni,
                specialty_id: professional.specialty.id.toString(),
                commission_percentage: professional.commission_percentage,
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
                dni: '',
                specialty_id: '',
                commission_percentage: '',
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
                    if (this.form[key] !== '' || key === 'is_active') {
                        // Convertir is_active a 1 o 0 para Laravel
                        if (key === 'is_active') {
                            formData.append(key, this.form[key] === 'true' ? '1' : '0');
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
                    // Mostrar errores de validación específicos
                    if (response.status === 422 && result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        this.showNotification('Errores de validación:\n' + errorMessages, 'error');
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
            try {
                const response = await fetch('/professionals', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                this.professionals = data.professionals;
                this.stats = data.stats;
            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        },
        
        filterProfessionals() {
            // Los filtros se aplican autom�ticamente por el computed property
        },
        
        clearFilters() {
            this.filters = {
                search: '',
                specialty: 'all',
                status: 'all'
            };
        },
        
        showNotification(message, type = 'info') {
            // Aquí podrías implementar un sistema de notificaciones
            alert(message);
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
@endpush
@endsection