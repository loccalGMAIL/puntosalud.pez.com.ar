@extends('layouts.app')

@section('title', 'Pacientes - ' . config('app.name'))
@section('mobileTitle', 'Pacientes')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4" x-data="patientsPage()">
    
    <!-- Header con estadísticas -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>Pacientes</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Pacientes
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Gestiona los pacientes del centro médico
                </p>
            </div>
            <button @click="openCreateModal()" 
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <!-- Plus Icon -->
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Paciente
            </button>
        </div>

        <!-- Cards de estadísticas -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">pacientes registrados</p>
                    </div>
                </div>
            </div>
            
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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Con Obra Social</dt>
                        <dd class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['with_insurance'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">tienen cobertura médica</p>
                    </div>
                </div>
            </div>
            
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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sin Obra Social</dt>
                        <dd class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['without_insurance'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">sin cobertura médica</p>
                    </div>
                </div>
            </div>
            
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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Este Mes</dt>
                        <dd class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['this_month'] }}</dd>
                        <p class="text-xs text-gray-500 dark:text-gray-400">pacientes nuevos</p>
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Búsqueda -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <input x-model="filters.search" 
                           type="text" 
                           placeholder="Buscar por nombre, DNI o email..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Filtro por obra social -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Obra Social</label>
                    <select x-model="filters.healthInsurance" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todas las obras sociales</option>
                        <option value="none">Sin obra social</option>
                        <template x-for="insurance in healthInsurances" :key="insurance">
                            <option :value="insurance" x-text="insurance"></option>
                        </template>
                    </select>
                </div>

                <!-- Filtro por estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select x-model="filters.status" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>

                <!-- Contador de resultados -->
                <div class="flex items-end">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <strong x-text="filteredPatients.length"></strong>
                        <span x-show="hasActiveFilters">resultados encontrados</span>
                        <span x-show="!hasActiveFilters">pacientes en total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de pacientes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Lista de Pacientes
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-200/50 dark:divide-emerald-800/30">
                    <thead class="bg-emerald-50/50 dark:bg-emerald-950/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Edad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Obra Social</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-emerald-200/30 dark:divide-emerald-800/30">
                        <!-- Estado vacío -->
                        <tr x-show="filteredPatients.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No se encontraron pacientes</p>
                                    <button @click="clearFilters()" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Limpiar filtros
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Filas de pacientes -->
                        <template x-for="patient in filteredPatients" :key="patient.id">
                            <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-950/20 transition-colors duration-200">
                                <!-- Paciente -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="patient.first_name + ' ' + patient.last_name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="'ID: ' + patient.id"></div>
                                    </div>
                                </td>
                                
                                <!-- DNI -->
                                <td class="px-6 py-4">
                                    <span class="text-sm font-mono text-gray-900 dark:text-white" x-text="patient.dni"></span>
                                </td>
                                
                                <!-- Contacto -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white" x-text="patient.email || 'Sin email'"></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="patient.phone"></div>
                                </td>
                                
                                <!-- Edad -->
                                <td class="px-6 py-4">
                                    <div class="text-center">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="calculateAge(patient.birth_date)"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">años</div>
                                    </div>
                                </td>
                                
                                <!-- Obra Social -->
                                <td class="px-6 py-4">
                                    <span x-show="patient.health_insurance" 
                                          class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-full" 
                                          x-text="patient.health_insurance">
                                    </span>
                                    <span x-show="!patient.health_insurance" 
                                          class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400 rounded-full">
                                        Sin obra social
                                    </span>
                                </td>
                                
                                <!-- Estado -->
                                <td class="px-6 py-4">
                                    <span :class="patient.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'" 
                                          class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                                          x-text="patient.is_active ? 'Activo' : 'Inactivo'">
                                    </span>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Botón Editar -->
                                        <button @click="openEditModal(patient)" 
                                                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Editar paciente">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Botón Activar/Desactivar -->
                                        <button @click="toggleStatus(patient)" 
                                                :class="patient.is_active ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' : 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                                class="p-2 rounded-lg transition-colors"
                                                :title="patient.is_active ? 'Desactivar paciente' : 'Activar paciente'">
                                            <!-- Icono Desactivar (cuando está activo) -->
                                            <svg x-show="patient.is_active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.5a8.25 8.25 0 0116.5 0v.5H4v-.5z" />
                                            </svg>
                                            <!-- Icono Activar (cuando está inactivo) -->
                                            <svg x-show="!patient.is_active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                {{ $patients->links() }}
            </div>
        </div>
    </div>

    @include('patients.modal')
</div>

<script>
function patientsPage() {
    return {
        // Data inicial
        patients: @json($patients->items()),
        stats: @json($stats),
        
        // Estados del modal
        modalOpen: false,
        editingPatient: null,
        loading: false,
        
        // Filtros
        filters: {
            search: '',
            healthInsurance: 'all',
            status: 'all'
        },

        // Debounce timer
        searchTimeout: null,
        
        // Formulario
        form: {
            first_name: '',
            last_name: '',
            dni: '',
            birth_date: '',
            email: '',
            phone: '',
            address: '',
            health_insurance: '',
            health_insurance_number: ''
        },
        
        // Computed
        get filteredPatients() {
            // Simplemente retornamos los pacientes ya que el filtrado se hace en backend
            return this.patients;
        },
        
        get hasActiveFilters() {
            return this.filters.search !== '' || 
                   this.filters.healthInsurance !== 'all' || 
                   this.filters.status !== 'all';
        },

        get healthInsurances() {
            const insurances = this.patients
                .filter(p => p.health_insurance)
                .map(p => p.health_insurance)
                .filter((value, index, self) => self.indexOf(value) === index)
                .sort();
            return insurances;
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
            this.$watch('filters.healthInsurance', () => {
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
                if (this.filters.healthInsurance !== 'all') {
                    params.append('health_insurance', this.filters.healthInsurance);
                }
                if (this.filters.status !== 'all') {
                    params.append('status', this.filters.status);
                }

                const response = await fetch(`/patients?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                this.patients = data.patients;
                this.stats = data.stats;
            } catch (error) {
                console.error('Error applying filters:', error);
            }
        },

        openCreateModal() {
            this.editingPatient = null;
            this.resetForm();
            this.modalOpen = true;
        },
        
        openEditModal(patient) {
            this.editingPatient = patient;
            this.form = {
                first_name: patient.first_name,
                last_name: patient.last_name,
                dni: patient.dni,
                birth_date: this.formatDateForInput(patient.birth_date),
                email: patient.email || '',
                phone: patient.phone,
                address: patient.address || '',
                health_insurance: patient.health_insurance || '',
                health_insurance_number: patient.health_insurance_number || ''
            };
            this.modalOpen = true;
        },
        
        resetForm() {
            this.form = {
                first_name: '',
                last_name: '',
                dni: '',
                birth_date: '',
                email: '',
                phone: '',
                address: '',
                health_insurance: '',
                health_insurance_number: ''
            };
        },
        
        async submitForm() {
            this.loading = true;
            
            try {
                const url = this.editingPatient ? 
                    `/patients/${this.editingPatient.id}` : 
                    '/patients';
                const method = this.editingPatient ? 'PUT' : 'POST';
                
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });
                
                if (this.editingPatient) {
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
                        this.showNotification(result.message || 'Error al guardar el paciente', 'error');
                    }
                }
            } catch (error) {
                this.showNotification('Error al guardar el paciente', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async refreshData() {
            // Recargar la página para reflejar los cambios
            window.location.reload();
        },
        
        clearFilters() {
            this.filters = {
                search: '',
                healthInsurance: 'all',
                status: 'all'
            };
            // Los watchers se encargarán de aplicar los filtros automáticamente
        },
        
        calculateAge(birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            
            return age;
        },

        async toggleStatus(patient) {
            const action = patient.is_active ? 'desactivar' : 'activar';
            if (!confirm(`¿Estás seguro de ${action} este paciente?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('activo', patient.is_active ? '0' : '1');
                formData.append('_method', 'PUT');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch(`/patients/${patient.id}`, {
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
                    this.showNotification(result.message || 'Error al actualizar el estado', 'error');
                }
            } catch (error) {
                this.showNotification('Error al actualizar el estado', 'error');
            }
        },
        
        showNotification(message, type = 'info') {
            alert(message);
        },

        getMaxDate() {
            return new Date().toISOString().split('T')[0];
        },

        formatDateForInput(dateString) {
            if (!dateString) return '';
            // Convertir fecha ISO (1980-01-01T03:00:00.000000Z) a formato yyyy-MM-dd
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
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