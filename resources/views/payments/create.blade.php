@extends('layouts.app')

@section('title', 'Nuevo Pago - ' . config('app.name'))
@section('mobileTitle', 'Nuevo Pago')

@section('content')
<div class="p-6" x-data="paymentForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('payments.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Pagos</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Nuevo Pago</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registrar Nuevo Pago</h1>
            <p class="text-gray-600 dark:text-gray-400">Complete la información para registrar un pago</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('payments.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Cancelar
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Información del Paciente -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    Información del Paciente
                </h2>

                <!-- Búsqueda de Paciente -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar Paciente *</label>
                    <div class="relative">
                        <input x-model="patientSearch" 
                               @input="searchPatients()" 
                               @focus="showPatientDropdown = true"
                               type="text" 
                               placeholder="Buscar por nombre, apellido o DNI..." 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                               required>
                        
                        <!-- Dropdown de pacientes -->
                        <div x-show="showPatientDropdown && searchResults.length > 0" 
                             x-cloak
                             class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto">
                            <template x-for="patient in searchResults" :key="patient.id">
                                <button type="button"
                                        @click="selectPatient(patient)"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    <div class="font-medium text-gray-900 dark:text-white" x-text="patient.first_name + ' ' + patient.last_name"></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="'DNI: ' + patient.dni"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Paciente Seleccionado -->
                <div x-show="selectedPatient" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-green-900 dark:text-green-200" x-text="selectedPatient?.first_name + ' ' + selectedPatient?.last_name"></div>
                            <div class="text-sm text-green-700 dark:text-green-300" x-text="'DNI: ' + selectedPatient?.dni"></div>
                        </div>
                        <button type="button" @click="clearPatient()" class="text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Pago -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m2.25 0v3m0 0v.75A.75.75 0 016 10.5h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H6.75m2.25 0h3m-3 7.5h3m-3-4.5h3M6.75 7.5H12m-3 3v6m-1.5-6h1.5m-1.5 0V9" />
                    </svg>
                    Detalles del Pago
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipo de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Pago *</label>
                        <select x-model="form.payment_type" 
                                @change="updatePaymentType()"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="">Seleccionar tipo</option>
                            <option value="single">Pago Individual</option>
                            <option value="package">Paquete de Sesiones</option>
                            <option value="refund">Reembolso</option>
                        </select>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método de Pago *</label>
                        <select x-model="form.payment_method" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="">Seleccionar método</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="card">Tarjeta</option>
                        </select>
                    </div>

                    <!-- Monto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                            <input x-model="form.amount" 
                                   type="number" 
                                   step="0.01" 
                                   min="0"
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>
                    </div>

                    <!-- Sesiones (solo para paquetes) -->
                    <div x-show="form.payment_type === 'package'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad de Sesiones *</label>
                        <input x-model="form.sessions_included" 
                               type="number" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Concepto -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto</label>
                    <textarea x-model="form.concept" 
                              rows="3"
                              placeholder="Descripción adicional del pago (opcional)"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- Turnos Pendientes (solo para pagos individuales) -->
        <div x-show="form.payment_type === 'single' && pendingAppointments.length > 0" 
             class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                    </svg>
                    Turnos Pendientes de Pago
                </h2>

                <div class="space-y-3">
                    <template x-for="(appointment, index) in pendingAppointments" :key="appointment.id">
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" 
                                           :id="'appointment-' + appointment.id"
                                           @change="toggleAppointment(appointment, index)"
                                           class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label :for="'appointment-' + appointment.id" class="flex-1 cursor-pointer">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            <span x-text="new Date(appointment.appointment_date).toLocaleDateString('es-ES')"></span>
                                            - Dr. <span x-text="appointment.professional.first_name + ' ' + appointment.professional.last_name"></span>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <span x-text="new Date(appointment.appointment_date).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})"></span>
                                            <span x-show="appointment.office" x-text="' - ' + appointment.office?.name"></span>
                                        </div>
                                    </label>
                                </div>
                                <div x-show="selectedAppointments.includes(appointment.id)" class="ml-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">Monto:</label>
                                        <div class="relative">
                                            <span class="absolute left-2 top-1 text-xs text-gray-500">$</span>
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0"
                                                   x-model="appointmentAmounts[appointment.id]"
                                                   class="w-24 pl-5 pr-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('payments.index') }}" 
               class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="loading"
                    class="px-6 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:bg-green-400 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:cursor-not-allowed">
                <span x-show="!loading">Registrar Pago</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Registrando...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
function paymentForm() {
    return {
        loading: false,
        patientSearch: '{{ $selectedPatient ? $selectedPatient->full_name : '' }}',
        selectedPatient: @json($selectedPatient),
        showPatientDropdown: false,
        searchResults: [],
        pendingAppointments: @json($pendingAppointments),
        selectedAppointments: [],
        appointmentAmounts: {},
        
        form: {
            payment_type: '',
            payment_method: '',
            amount: '',
            concept: '',
            sessions_included: ''
        },

        init() {
            // Click outside to close dropdown
            document.addEventListener('click', (event) => {
                if (!event.target.closest('[x-data]')) {
                    this.showPatientDropdown = false;
                }
            });
        },

        async searchPatients() {
            if (this.patientSearch.length < 2) {
                this.searchResults = [];
                return;
            }

            try {
                const response = await fetch(`/payments/search-patients?q=${encodeURIComponent(this.patientSearch)}`);
                this.searchResults = await response.json();
            } catch (error) {
                console.error('Error searching patients:', error);
            }
        },

        async selectPatient(patient) {
            this.selectedPatient = patient;
            this.patientSearch = patient.first_name + ' ' + patient.last_name;
            this.showPatientDropdown = false;
            this.searchResults = [];

            // Load pending appointments
            try {
                const response = await fetch(`/payments/patients/${patient.id}/pending-appointments`);
                this.pendingAppointments = await response.json();
            } catch (error) {
                console.error('Error loading appointments:', error);
            }
        },

        clearPatient() {
            this.selectedPatient = null;
            this.patientSearch = '';
            this.pendingAppointments = [];
            this.selectedAppointments = [];
            this.appointmentAmounts = {};
        },

        updatePaymentType() {
            if (this.form.payment_type !== 'single') {
                this.selectedAppointments = [];
                this.appointmentAmounts = {};
            }
        },

        toggleAppointment(appointment, index) {
            const appointmentId = appointment.id;
            const isSelected = this.selectedAppointments.includes(appointmentId);
            
            if (isSelected) {
                this.selectedAppointments = this.selectedAppointments.filter(id => id !== appointmentId);
                delete this.appointmentAmounts[appointmentId];
            } else {
                this.selectedAppointments.push(appointmentId);
                this.appointmentAmounts[appointmentId] = appointment.estimated_amount || 0;
            }
        },

        async submitForm() {
            if (!this.selectedPatient) {
                alert('Debe seleccionar un paciente');
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('patient_id', this.selectedPatient.id);
                formData.append('payment_type', this.form.payment_type);
                formData.append('payment_method', this.form.payment_method);
                formData.append('amount', this.form.amount);
                formData.append('concept', this.form.concept);
                
                if (this.form.payment_type === 'package') {
                    formData.append('sessions_included', this.form.sessions_included);
                }

                // Add selected appointments
                if (this.form.payment_type === 'single' && this.selectedAppointments.length > 0) {
                    this.selectedAppointments.forEach((appointmentId, index) => {
                        formData.append(`appointment_ids[${index}]`, appointmentId);
                        formData.append(`allocated_amounts[${index}]`, this.appointmentAmounts[appointmentId] || 0);
                    });
                }

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('/payments', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = `/payments/${result.payment.id}`;
                } else {
                    alert(result.message || 'Error al registrar el pago');
                }
            } catch (error) {
                alert('Error al registrar el pago');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection