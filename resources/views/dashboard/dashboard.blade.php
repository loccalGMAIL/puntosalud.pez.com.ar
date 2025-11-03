@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $dashboardData['fecha'] }}</div>
        </div>

        <!-- Alertas de caja (solo para recepcionistas) -->
        @if($dashboardData['cashStatus'])
            <div id="cash-alerts" x-data="cashAlerts()" x-init="init()">
                <!-- Alerta de caja sin cerrar de día anterior -->
                @if($dashboardData['cashStatus']['unclosed_date'])
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.75 0h15.06m-12.06-3.75H12m0 0v3.75m9.75-3.75v3.75" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Caja sin cerrar</h3>
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    La caja del {{ \Carbon\Carbon::parse($dashboardData['cashStatus']['unclosed_date'])->format('d/m/Y') }} no fue cerrada.
                                </p>
                            </div>
                        </div>
                        <button @click="openCloseCashModal('{{ $dashboardData['cashStatus']['unclosed_date'] }}')" 
                                class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                            Cerrar Caja
                        </button>
                    </div>
                </div>
                @endif

                <!-- Alerta de apertura de caja del día actual -->
                @if($dashboardData['cashStatus']['today']['needs_opening'])
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Abrir caja del día</h3>
                                <p class="text-sm text-amber-700 dark:text-amber-300">
                                    La caja del día de hoy no ha sido abierta aún.
                                </p>
                            </div>
                        </div>
                        <button @click="openOpenCashModal()" 
                                class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                            Abrir Caja
                        </button>
                    </div>
                </div>
                @endif


                <!-- Modal para abrir caja -->
                <div x-show="openCashModalVisible" 
                     x-cloak 
                     class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4"
                     @click.self="closeOpenCashModal()">
                    <div class="modal-content bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Abrir Caja del Día</h2>
                            
                            <form @submit.prevent="submitOpenCash()">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Monto inicial de caja (opcional)
                                        </label>
                                        <input type="number"
                                               x-model="openCashForm.opening_amount"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                               placeholder="0.00">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Notas (opcional)
                                        </label>
                                        <textarea x-model="openCashForm.notes"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                  rows="2"
                                                  placeholder="Observaciones sobre la apertura..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3 mt-6">
                                    <button type="button" 
                                            @click="closeOpenCashModal()"
                                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                        Cancelar
                                    </button>
                                    <button type="submit" 
                                            :disabled="openCashLoading"
                                            class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-lg transition-colors">
                                        <span x-show="!openCashLoading">Abrir Caja</span>
                                        <span x-show="openCashLoading">Abriendo...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal para cerrar caja -->
                <div x-show="closeCashModalVisible" 
                     x-cloak 
                     class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4"
                     @click.self="closeCloseCashModal()">
                    <div class="modal-content bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Cerrar Caja <span x-text="closeCashDate ? '- ' + closeCashDate : ''"></span>
                            </h2>
                            
                            <form @submit.prevent="submitCloseCash()">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Efectivo contado en caja *
                                        </label>
                                        <input type="number" 
                                               x-model="closeCashForm.closing_amount"
                                               step="0.01" 
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                               placeholder="0.00"
                                               required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Notas (opcional)
                                        </label>
                                        <textarea x-model="closeCashForm.notes"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                  rows="2"
                                                  placeholder="Observaciones sobre el cierre..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3 mt-6">
                                    <button type="button" 
                                            @click="closeCloseCashModal()"
                                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                        Cancelar
                                    </button>
                                    <button type="submit" 
                                            :disabled="closeCashLoading"
                                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-lg transition-colors">
                                        <span x-show="!closeCashLoading">Cerrar Caja</span>
                                        <span x-show="closeCashLoading">Cerrando...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Métricas principales -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            
            <!-- Card 1: Consultas del día -->
            <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/50 p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/20 dark:hover:shadow-emerald-900/20">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Consultas del Día</p>
                        <div class="mt-1 flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ $dashboardData['consultasHoy']['total'] }}</p>
                        </div>
                        <div class="mt-2 grid grid-cols-1 gap-2 text-xs">
                            <div class="flex items-center gap-1">
                                <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['consultasHoy']['completadas'] }} completadas</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['consultasHoy']['pendientes'] }} pendientes</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="h-3 w-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $dashboardData['consultasHoy']['ausentes'] }} ausentes</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                        <svg class="h-5 w-5 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l4 4L21 3" />
                        </svg>
                    </div>
                </div>
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>

            <!-- Card 2: Ingresos del día -->
            <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10 dark:hover:shadow-emerald-900/20">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ingresos del Día</p>
                        <div class="mt-1 flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">${{ number_format($dashboardData['ingresosHoy']['total'], 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-2 space-y-1 text-xs">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Efectivo:</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($dashboardData['ingresosHoy']['efectivo'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Transferencia:</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($dashboardData['ingresosHoy']['transferencia'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Tarjeta:</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">${{ number_format($dashboardData['ingresosHoy']['tarjeta'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                        <!-- Dollar Sign Icon -->
                        <svg class="h-5 w-5 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <!-- Decorative gradient -->
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>
            <!-- Card 3: Accesos rápidos -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-emerald-200/50 p-4 shadow-sm dark:border-emerald-800/30 flex flex-col gap-3">
                <!-- Nuevo Paciente -->
                <div x-data="patientsModalDashboard()">
                    <a href="#" @click.prevent="openCreateModal()" class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-emerald-50 to-emerald-100 hover:from-emerald-100 hover:to-emerald-200 border border-emerald-200 dark:from-emerald-900/20 dark:to-emerald-800/20 dark:border-emerald-700 transition-all duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.75 18a6.25 6.25 0 1112.5 0v.75a.75.75 0 01-.75.75H5.5a.75.75 0 01-.75-.75V18z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-emerald-900 dark:text-emerald-100">Nuevo Paciente</div>
                                <div class="text-xs text-emerald-700 dark:text-emerald-300">Registrar paciente</div>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    @include('patients.modal')
                </div>

                <!-- Entreturno / Urgencia -->
                <div x-data="urgencyModalDashboard()" x-init="init()">
                    <a href="#" @click.prevent="openUrgencyModal()" class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-red-50 to-red-100 hover:from-red-100 hover:to-red-200 border border-red-200 dark:from-red-900/20 dark:to-red-800/20 dark:border-red-700 transition-all duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-red-900 dark:text-red-100">Urgencia</div>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-red-600 dark:text-red-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    @include('appointments.modal-urgency')
                </div>
            </div>

<script>
function patientsModalDashboard() {
    return {
        modalOpen: false,
        editingPatient: false,
        form: {
            first_name: '',
            last_name: '',
            dni: '',
            birth_date: '',
            phone: '',
            email: '',
            address: '',
            health_insurance: '',
            health_insurance_number: ''
        },
        loading: false,
        openCreateModal() {
            this.editingPatient = false;
            this.modalOpen = true;
            this.form = {
                first_name: '',
                last_name: '',
                dni: '',
                birth_date: '',
                phone: '',
                email: '',
                address: '',
                health_insurance: '',
                health_insurance_number: ''
            };
        },
        async submitForm() {
            this.loading = true;
            try {
                const response = await fetch("{{ route('patients.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });
                const result = await response.json();
                if (result.success) {
                    this.loading = false;
                    this.modalOpen = false;
                    await SystemModal.show('success', 'Éxito', result.message || 'Paciente creado exitosamente.', 'Aceptar', false);
                } else {
                    this.loading = false;
                    let msg = result.message || 'Error al crear paciente.';
                    if (result.errors) {
                        msg += '\n' + Object.values(result.errors).map(e => e.join(', ')).join('\n');
                    }
                    await SystemModal.show('error', 'Error', msg, 'Aceptar', false);
                }
            } catch (error) {
                this.loading = false;
                await SystemModal.show('error', 'Error', 'Error inesperado al crear paciente.', 'Aceptar', false);
            }
        },
        getMaxDate() {
            return new Date().toISOString().split('T')[0];
        },
        calculateAge(date) {
            if (!date) return '';
            const today = new Date();
            const birth = new Date(date);
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age;
        }
    };
}

// Alpine.js para modal de urgencia en dashboard
function urgencyModalDashboard() {
    return {
        urgencyModalOpen: false,
        urgencyLoading: false,
        professionals: [],
        patients: [],
        offices: [],
        urgencyForm: {
            professional_id: '',
            patient_id: '',
            appointment_date: new Date().toISOString().split('T')[0],
            estimated_amount: '0',
            office_id: '',
            notes: ''
        },
        async init() {
            await this.loadData();
            this.initializeSelect2();
        },
        async loadData() {
            try {
                const response = await fetch('/appointments?ajax=1', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                this.professionals = data.professionals || [];
                this.patients = data.patients || [];
                this.offices = data.offices || [];
            } catch (error) {
                console.error('Error loading data:', error);
            }
        },
        initializeSelect2() {
            const self = this;
            let urgencyProfessionalSelect = null;
            let urgencyPatientSelect = null;

            // Verificar cuando el modal se abre para inicializar Select2
            this.$watch('urgencyModalOpen', function(isOpen) {
                if (isOpen) {
                    setTimeout(() => {
                        // Inicializar Select2 para profesional
                        if (!urgencyProfessionalSelect && $('#urgency-professional-select').length) {
                            urgencyProfessionalSelect = $('#urgency-professional-select').select2({
                                placeholder: 'Buscar profesional...',
                                allowClear: false,
                                width: '100%',
                                dropdownParent: $('#urgency-professional-select').closest('.bg-white'),
                                language: {
                                    noResults: function() { return "No se encontraron resultados"; },
                                    searching: function() { return "Buscando..."; }
                                }
                            });

                            urgencyProfessionalSelect.on('change', function(e) {
                                self.urgencyForm.professional_id = $(this).val();
                            });

                            urgencyProfessionalSelect.on('select2:open', function() {
                                document.querySelector('.select2-search__field').focus();
                            });
                        }

                        // Inicializar Select2 para paciente con búsqueda por DNI
                        if (!urgencyPatientSelect && $('#urgency-patient-select').length) {
                            urgencyPatientSelect = $('#urgency-patient-select').select2({
                                placeholder: 'Buscar paciente por nombre o DNI...',
                                allowClear: false,
                                width: '100%',
                                dropdownParent: $('#urgency-patient-select').closest('.bg-white'),
                                language: {
                                    noResults: function() { return "No se encontraron resultados"; },
                                    searching: function() { return "Buscando..."; }
                                },
                                matcher: function(params, data) {
                                    if ($.trim(params.term) === '') {
                                        return data;
                                    }
                                    if (!data.id) {
                                        return null;
                                    }

                                    const searchTerm = params.term.toLowerCase();
                                    const text = (data.text || '').toLowerCase();
                                    const $option = $(data.element);
                                    const dni = ($option.attr('data-dni') || '').toLowerCase();
                                    const firstName = ($option.attr('data-first-name') || '').toLowerCase();
                                    const lastName = ($option.attr('data-last-name') || '').toLowerCase();

                                    if (text.indexOf(searchTerm) > -1 ||
                                        dni.indexOf(searchTerm) > -1 ||
                                        firstName.indexOf(searchTerm) > -1 ||
                                        lastName.indexOf(searchTerm) > -1) {
                                        return data;
                                    }

                                    return null;
                                }
                            });

                            urgencyPatientSelect.on('change', function(e) {
                                self.urgencyForm.patient_id = $(this).val();
                            });

                            urgencyPatientSelect.on('select2:open', function() {
                                document.querySelector('.select2-search__field').focus();
                            });
                        }
                    }, 150);
                } else {
                    // Limpiar Select2 cuando se cierra el modal
                    if (urgencyProfessionalSelect) {
                        urgencyProfessionalSelect.select2('destroy');
                        urgencyProfessionalSelect = null;
                    }
                    if (urgencyPatientSelect) {
                        urgencyPatientSelect.select2('destroy');
                        urgencyPatientSelect = null;
                    }
                }
            });
        },
        openUrgencyModal() {
            this.urgencyModalOpen = true;
            this.urgencyForm = {
                professional_id: '',
                patient_id: '',
                appointment_date: new Date().toISOString().split('T')[0],
                estimated_amount: '0',
                office_id: '',
                notes: ''
            };
        },
        closeUrgencyModal() {
            this.urgencyModalOpen = false;
        },
        async submitUrgencyForm() {
            if (this.urgencyLoading) return;
            if (!this.urgencyForm.professional_id || !this.urgencyForm.patient_id || !this.urgencyForm.appointment_date || !this.urgencyForm.estimated_amount) {
                await SystemModal.show('error', 'Error', 'Complete todos los campos requeridos (Profesional, Paciente, Fecha y Monto).', 'Aceptar', false);
                return;
            }
            this.urgencyLoading = true;
            try {
                const response = await fetch("{{ route('appointments.urgency.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.urgencyForm)
                });
                const result = await response.json();
                if (result.success) {
                    this.urgencyLoading = false;
                    this.urgencyModalOpen = false;
                    await SystemModal.show('success', 'Éxito', result.message || 'Urgencia registrada exitosamente.', 'Aceptar', false);
                    location.reload();
                } else {
                    this.urgencyLoading = false;
                    let msg = result.message || 'Error al registrar la urgencia.';
                    if (result.errors) {
                        msg += '\n' + Object.values(result.errors).map(e => e.join(', ')).join('\n');
                    }
                    await SystemModal.show('error', 'Error', msg, 'Aceptar', false);
                }
            } catch (error) {
                this.urgencyLoading = false;
                await SystemModal.show('error', 'Error', 'Error inesperado al registrar la urgencia.', 'Aceptar', false);
            }
        }
    };
}
</script>

            <!-- Card 3: Acceso Rápido a Reportes -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-emerald-200/50 p-4 shadow-sm dark:border-emerald-800/30">
                <div class="space-y-3">
                    <a href="{{ route('reports.daily-schedule') }}" 
                       class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border border-blue-200 dark:from-blue-900/20 dark:to-blue-800/20 dark:border-blue-700 transition-all duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Pacientes a Atender</div>
                                <div class="text-xs text-blue-700 dark:text-blue-300">Lista para imprimir</div>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                    
                    <a href="{{ route('reports.professional-liquidation') }}" 
                       class="flex items-center justify-between p-3 rounded-lg bg-gradient-to-r from-amber-50 to-amber-100 hover:from-amber-100 hover:to-amber-200 border border-amber-200 dark:from-amber-900/20 dark:to-amber-800/20 dark:border-amber-700 transition-all duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-amber-900 dark:text-amber-100">Liquidación para Profesionales</div>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- Área principal dividida -->
        <div class="grid gap-4 lg:grid-cols-3">
            
            <!-- Izquierda: Lista de últimas consultas (2/3 del ancho) -->
            <div class="lg:col-span-2 rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/20 p-6 shadow-sm dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <!-- Calendar Icon -->
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 3v2M18 3v2M3 18V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6" />
                        </svg>
                        Consultas de Hoy
                    </h2>
                    <a href="{{ route('dashboard.appointments') }}" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium transition-colors duration-200 dark:text-emerald-400 dark:hover:text-emerald-300">
                        Ver todas →
                    </a>
                </div>

                @php
                    $consultasDetalleOrdenadas = collect($dashboardData['consultasDetalle'])
                        ->sortByDesc(function($consulta) {
                            // Prioridad: urgencias primero, luego atendidas, luego el resto
                            return ($consulta['isUrgency'] ? 2 : 0) + ($consulta['status'] === 'attended' ? 1 : 0);
                        })
                        ->filter(function($consulta) {
                            return !($consulta['status'] === 'attended' && $consulta['isPaid']) && $consulta['status'] !== 'absent';
                        })
                        ->values();
                @endphp

                <div x-data="{
                    currentPage: 1,
                    perPage: 10,
                    get totalPages() { return Math.ceil({{ $consultasDetalleOrdenadas->count() }} / this.perPage); },
                    get startIndex() { return (this.currentPage - 1) * this.perPage; },
                    get endIndex() { return this.startIndex + this.perPage; }
                }">
                    <div class="space-y-3">

                        @forelse($consultasDetalleOrdenadas as $index => $consulta)
                        <div x-show="{{ $index }} >= startIndex && {{ $index }} < endIndex"
                            class="group flex items-center justify-between p-4 rounded-lg border bg-white/80 transition-all duration-200 hover:shadow-md dark:bg-gray-800/50
                            @if($consulta['isUrgency']) border-red-300 hover:border-red-400 bg-red-50/50 dark:border-red-700 dark:hover:border-red-600 dark:bg-red-900/10
                            @elseif($consulta['status'] === 'attended') border-emerald-100 hover:border-emerald-200 dark:border-emerald-800/30 dark:hover:border-emerald-700/50
                            @elseif($consulta['status'] === 'scheduled') border-blue-100 hover:border-blue-200 dark:border-blue-800/30 dark:hover:border-blue-700/50
                            @elseif($consulta['status'] === 'cancelled') border-red-100 hover:border-red-200 dark:border-red-800/30 dark:hover:border-red-700/50
                            @else border-amber-100 hover:border-amber-200 dark:border-amber-800/30 dark:hover:border-amber-700/50 @endif">
                            
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full transition-transform duration-200 group-hover:scale-110
                                    @if($consulta['status'] === 'attended') bg-emerald-100 dark:bg-emerald-900/30
                                    @elseif($consulta['status'] === 'scheduled') bg-blue-100 dark:bg-blue-900/30
                                    @elseif($consulta['status'] === 'cancelled') bg-red-100 dark:bg-red-900/30
                                    @else bg-amber-100 dark:bg-amber-900/30 @endif">
                                    
                                    @if($consulta['status'] === 'attended')
                                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($consulta['status'] === 'scheduled')
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($consulta['status'] === 'cancelled')
                                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $consulta['paciente'] }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $consulta['profesional'] }} • {{ $consulta['hora'] }}</p>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">${{ number_format($consulta['monto'], 0, ',', '.') }}</p>
                                        <div class="flex items-center gap-1 flex-wrap">
                                            @if($consulta['isUrgency'])
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700">
                                                    URGENCIA
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                @if($consulta['status'] === 'attended') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                                @elseif($consulta['status'] === 'scheduled') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                                @elseif($consulta['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                                {{ $consulta['statusLabel'] }}
                                            </span>
                                            @if($consulta['isPaid'])
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                    💰 Pagado
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Botones de acción -->
                                    <div class="flex gap-1" x-data="appointmentActions({{ $consulta['id'] }}, {{ $consulta['monto'] ?? 0 }})">
                                        @if($consulta['canMarkAttended'])
                                            <button @click="markAttended()" :disabled="loading"
                                                    class="p-1.5 text-xs bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                    title="Marcar como atendido">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        @if($consulta['canMarkCompleted'])
                                            <button @click="markCompletedAndPaid()" :disabled="loading"
                                                    class="p-1.5 text-xs bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                    title="Finalizar y cobrar">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        @if($consulta['status'] === 'scheduled')
                                            <button @click="markAbsent()" :disabled="loading"
                                                    class="p-1.5 text-xs bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-md transition-colors duration-200 disabled:cursor-not-allowed"
                                                    title="Marcar como ausente">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Estado vacío -->
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 3v2M18 3v2M3 18V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No hay consultas para hoy</p>
                        </div>
                    @endforelse
                    </div>

                    <!-- Paginación -->
                    @if($consultasDetalleOrdenadas->count() > 10)
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Mostrando <span x-text="startIndex + 1"></span> - <span x-text="Math.min(endIndex, {{ $consultasDetalleOrdenadas->count() }})"></span> de <span>{{ $consultasDetalleOrdenadas->count() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                                class="px-3 py-1 rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Anterior
                            </button>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Página <span x-text="currentPage"></span> de <span x-text="totalPages"></span>
                            </span>
                            <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages"
                                class="px-3 py-1 rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Siguiente
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Derecha: Resumen de caja (1/3 del ancho) -->
            <div class="rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <!-- Dollar Sign Icon -->
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Resumen de Caja
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-emerald-100 px-2 py-1 rounded-md dark:bg-emerald-900/30">{{ $dashboardData['fecha'] }}</span>
                </div>

                <!-- Totales por profesional -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide flex items-center gap-2">
                        <!-- Heart Icon -->
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733 -0.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        Por Profesional
                    </h3>
                    
                    <div class="space-y-3">
                        @forelse($dashboardData['resumenCaja']['porProfesional'] as $profesional)
                            <div class="flex justify-between items-center p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/30">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $profesional['nombre'] }}</span>
                                <div class="text-right">
                                    <div class="font-semibold text-emerald-700 dark:text-emerald-400">${{ number_format($profesional['total'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Prof: ${{ number_format($profesional['profesional'], 0, ',', '.') }} | Clínica: ${{ number_format($profesional['clinica'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Estado vacío -->
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay ingresos registrados hoy</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Formas de pago -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">Formas de Pago</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Efectivo</span>
                            </div>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($dashboardData['resumenCaja']['formasPago']['efectivo'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Transferencia</span>
                            </div>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">${{ number_format($dashboardData['resumenCaja']['formasPago']['transferencia'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-purple-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tarjeta</span>
                            </div>
                            <span class="font-semibold text-purple-600 dark:text-purple-400">${{ number_format($dashboardData['resumenCaja']['formasPago']['tarjeta'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Total general -->
                <div class="border-t border-emerald-200 dark:border-emerald-700/50 pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Total del Día</span>
                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($dashboardData['resumenCaja']['totalGeneral'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $dashboardData['consultasHoy']['completadas'] }} consultas completadas</span>
                        <span>{{ count($dashboardData['consultasDetalle']) }} citas del día</span>
                    </div>
                </div>

                
                <!-- Botón de acción -->
                <a href="{{ route('cash.daily') }}" class="block w-full mt-6 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg text-center">
                    Ver Detalle de Caja
                </a>
            </div>
        </div>
    </div>

    <!-- Modal para finalizar y cobrar -->
    <x-payment-modal />

    <!-- Modal del sistema para notificaciones y confirmaciones -->
    <x-system-modal />

    <script>
    // Dashboard Management - Modern ES6+ approach
    const DashboardAPI = {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        
        async makeRequest(url, options = {}) {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                ...options
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Error en la operación');
            }
            
            return result;
        },
        
        async showNotification(message, type = 'info') {
            const modalType = type === 'error' ? 'error' : type === 'success' ? 'success' : 'confirm';
            const title = type === 'error' ? 'Error' : type === 'success' ? 'Éxito' : 'Información';
            await SystemModal.show(modalType, title, message, 'Aceptar', false);
        },
        
        reloadPage(delay = 500) {
            setTimeout(() => location.reload(), delay);
        }
    };

    // Global modal reference
    let globalPaymentModal = null;

    // Alpine.js appointment actions component
    function appointmentActions(appointmentId, estimatedAmount = 0) {
        return {
            loading: false,
            
            async markAttended() {
                if (this.loading) return;
                this.loading = true;

                try {
                    await DashboardAPI.makeRequest(`/dashboard/appointments/${appointmentId}/mark-attended`, {
                        method: 'POST'
                    });

                    await DashboardAPI.showNotification('Turno marcado como atendido exitosamente', 'success');
                    DashboardAPI.reloadPage();
                } catch (error) {
                    await DashboardAPI.showNotification(error.message, 'error');
                    this.loading = false;
                }
            },

            markCompletedAndPaid() {
                globalPaymentModal?.showModal(appointmentId, estimatedAmount);
            },

            async markAbsent() {
                const confirmed = await SystemModal.confirm(
                    'Confirmar ausencia',
                    '¿Está seguro de marcar este turno como ausente?',
                    'Sí, marcar ausente',
                    'Cancelar'
                );

                if (!confirmed) return;
                if (this.loading) return;

                this.loading = true;

                try {
                    await DashboardAPI.makeRequest(`/dashboard/appointments/${appointmentId}/mark-absent`, {
                        method: 'POST'
                    });

                    await DashboardAPI.showNotification('Turno marcado como ausente', 'success');
                    DashboardAPI.reloadPage();
                } catch (error) {
                    await DashboardAPI.showNotification(error.message, 'error');
                    this.loading = false;
                }
            }
        };
    }
    
    // Alpine.js payment modal component
    function paymentModal() {
        return {
            show: false,
            loading: false,
            currentAppointmentId: null,
            paymentForm: { final_amount: '', payment_method: '', concept: '' },
            
            init() {
                globalPaymentModal = this;
            },
            
            showModal(appointmentId, estimatedAmount = 0) {
                this.currentAppointmentId = appointmentId;
                this.paymentForm = {
                    final_amount: estimatedAmount || '',
                    payment_method: '',
                    concept: ''
                };
                this.show = true;
            },
            
            hide() {
                this.show = false;
                this.currentAppointmentId = null;
                this.loading = false;
            },
            
            async submitPayment() {
                if (this.loading) return;

                // Validation
                if (!this.paymentForm.final_amount || !this.paymentForm.payment_method) {
                    await DashboardAPI.showNotification('Complete todos los campos requeridos', 'error');
                    return;
                }

                this.loading = true;

                try {
                    const result = await DashboardAPI.makeRequest(
                        `/dashboard/appointments/${this.currentAppointmentId}/mark-completed-paid`,
                        {
                            method: 'POST',
                            body: JSON.stringify(this.paymentForm)
                        }
                    );

                    // Cerrar el modal de pago primero
                    this.hide();

                    // Preguntar si desea imprimir el recibo
                    if (result.payment_id) {
                        const printReceipt = await SystemModal.confirm(
                            'Imprimir recibo',
                            '¿Desea imprimir el recibo ahora?',
                            'Sí, imprimir',
                            'No'
                        );

                        if (printReceipt) {
                            window.open(`/payments/${result.payment_id}/print-receipt?print=1`, '_blank');
                        }
                    }

                    DashboardAPI.reloadPage();
                } catch (error) {
                    await DashboardAPI.showNotification(error.message, 'error');
                    this.loading = false;
                }
            }
        };
    }

    // Alpine.js cash alerts component
    function cashAlerts() {
        return {
            openCashModalVisible: false,
            closeCashModalVisible: false,
            openCashLoading: false,
            closeCashLoading: false,
            closeCashDate: null,
            
            openCashForm: {
                opening_amount: '',
                notes: ''
            },
            
            closeCashForm: {
                closing_amount: '',
                notes: '',
                close_date: null
            },
            
            init() {
                // Component initialized
            },
            
            openOpenCashModal() {
                this.openCashForm = { opening_amount: '', notes: '' };
                this.openCashModalVisible = true;
            },
            
            closeOpenCashModal() {
                this.openCashModalVisible = false;
                this.openCashLoading = false;
            },
            
            openCloseCashModal(date = null) {
                this.closeCashDate = date;
                this.closeCashForm = { 
                    closing_amount: '', 
                    notes: '', 
                    close_date: date 
                };
                this.closeCashModalVisible = true;
            },
            
            closeCloseCashModal() {
                this.closeCashModalVisible = false;
                this.closeCashLoading = false;
                this.closeCashDate = null;
            },
            
            async submitOpenCash() {
                if (this.openCashLoading) return;

                this.openCashLoading = true;

                try {
                    await DashboardAPI.makeRequest('/cash/open', {
                        method: 'POST',
                        body: JSON.stringify(this.openCashForm)
                    });

                    this.closeOpenCashModal();
                    await DashboardAPI.showNotification('Caja abierta exitosamente', 'success');
                    DashboardAPI.reloadPage();
                } catch (error) {
                    await DashboardAPI.showNotification(error.message, 'error');
                    this.openCashLoading = false;
                }
            },

            async submitCloseCash() {
                if (this.closeCashLoading) return;

                if (!this.closeCashForm.closing_amount) {
                    await DashboardAPI.showNotification('Complete el monto contado', 'error');
                    return;
                }

                this.closeCashLoading = true;

                try {
                    const result = await DashboardAPI.makeRequest('/cash/close', {
                        method: 'POST',
                        body: JSON.stringify(this.closeCashForm)
                    });

                    this.closeCloseCashModal();

                    // Mostrar resumen del cierre
                    const summary = result.summary;
                    const theoreticalBalance = parseFloat(summary.theoretical_balance) || 0;
                    const countedAmount = parseFloat(summary.counted_amount) || 0;
                    const difference = parseFloat(summary.difference) || 0;

                    let message = `Caja cerrada para el ${summary.date}.\n`;
                    message += `Teórico: $${theoreticalBalance.toFixed(2)}\n`;
                    message += `Contado: $${countedAmount.toFixed(2)}`;

                    if (Math.abs(difference) > 0.01) {
                        message += `\nDiferencia: $${difference.toFixed(2)}`;
                    }

                    await DashboardAPI.showNotification(message, 'success');
                    DashboardAPI.reloadPage();
                } catch (error) {
                    await DashboardAPI.showNotification(error.message, 'error');
                    this.closeCashLoading = false;
                }
            }
        };
    }

    </script>

    @push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        [x-cloak] { display: none !important; }

        /* Asegurar que el modal esté por encima de todo */
        .modal-overlay {
            z-index: 10000 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }

        .modal-content {
            position: relative !important;
            z-index: 10001 !important;
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding: 8px 12px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            color: #1f2937 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ef4444 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 6px 12px !important;
        }

        /* Dark mode styles for Select2 */
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f9fafb !important;
        }

        .dark .select2-dropdown {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
        }

        .dark .select2-container--default .select2-results__option {
            background-color: #374151 !important;
            color: #f9fafb !important;
        }

        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #dc2626 !important;
        }

        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #1f2937 !important;
            border-color: #4b5563 !important;
            color: #f9fafb !important;
        }

        .select2-container {
            z-index: 10002 !important;
        }

        .select2-dropdown {
            z-index: 10003 !important;
        }
    </style>
    @endpush

    @push('scripts')
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
    @endpush
@endsection