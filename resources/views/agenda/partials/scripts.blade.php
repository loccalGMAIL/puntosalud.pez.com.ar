@push('scripts')
@php
$schedulesForJs = [];
if (isset($professionalSchedules)) {
    foreach ($professionalSchedules as $dayOfWeek => $schedule) {
        $schedulesForJs[$dayOfWeek] = [
            'start_time' => \Carbon\Carbon::parse($schedule->getRawOriginal('start_time'))->format('H:i'),
            'end_time'   => \Carbon\Carbon::parse($schedule->getRawOriginal('end_time'))->format('H:i'),
        ];
    }
}
@endphp
<!-- Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
function openAppointmentModal(date, professionalId) {
    // Dispatch event to Alpine.js component
    document.dispatchEvent(new CustomEvent('open-appointment-modal', {
        detail: {
            date: date,
            professionalId: professionalId
        }
    }));
}

function openEditAppointmentModal(appointmentId) {
    // Dispatch event to Alpine.js component
    document.dispatchEvent(new CustomEvent('open-edit-appointment-modal', {
        detail: {
            appointmentId: appointmentId
        }
    }));
}

function openDayModal(date, professionalId) {
    // Dispatch event to Alpine.js component
    document.dispatchEvent(new CustomEvent('open-day-modal', {
        detail: {
            date: date,
            professionalId: professionalId
        }
    }));
}

// Initialize Alpine.js component for the modal
document.addEventListener('alpine:init', () => {
    Alpine.data('appointmentModal', () => ({
        modalOpen: false,
        dayModalOpen: false,
        editingAppointment: null,
        loading: false,
        formErrors: {},
        professionals: @json($professionals),
        patients: @json($patients),
        offices: @json($offices),
        allAppointments: @json($appointments),
        schedules: @json($schedulesForJs ?? []),
        daySchedule: null,
        pxPerMin: 3,
        durationOptions: [
            { value: 5,   label: '5 minutos' },
            { value: 10,  label: '10 minutos' },
            { value: 15,  label: '15 minutos' },
            { value: 20,  label: '20 minutos' },
            { value: 25,  label: '25 minutos' },
            { value: 30,  label: '30 minutos' },
            { value: 40,  label: '40 minutos' },
            { value: 45,  label: '45 minutos' },
            { value: 60,  label: '1 hora' },
            { value: 90,  label: '1 hora 30 minutos' },
            { value: 120, label: '2 horas' },
        ],

        // Error state for past datetime validation
        pastTimeError: '',

        // Day modal data
        selectedDayDate: '',
        selectedProfessionalId: null,
        selectedProfessionalName: '',
        dayAppointments: [],

        form: {
            professional_id: '',
            patient_id: '',
            appointment_date: '',
            appointment_time: '',
            duration: 30,
            office_id: '',
            notes: '',
            estimated_amount: '',
            status: 'scheduled'
        },

        init() {
            // Listen for modal open events
            document.addEventListener('open-appointment-modal', (event) => {
                this.openCreateModal(event.detail.date, event.detail.professionalId);
            });

            document.addEventListener('open-edit-appointment-modal', (event) => {
                this.openEditModalById(event.detail.appointmentId);
            });

            document.addEventListener('open-day-modal', (event) => {
                this.openDayModal(event.detail.date, event.detail.professionalId);
            });
        },

        openCreateModal(date = null, professionalId = null) {
            this.editingAppointment = null;
            this.resetForm();
            this.clearAllErrors();

            if (date) {
                this.form.appointment_date = date;
            }
            if (professionalId) {
                this.form.professional_id = professionalId.toString();
            }

            this.modalOpen = true;
        },

        openCreateModalWithTime(date, professionalId, time) {
            this.editingAppointment = null;
            this.resetForm();
            this.clearAllErrors();
            if (date) this.form.appointment_date = date;
            if (professionalId) this.form.professional_id = professionalId.toString();
            if (time) this.form.appointment_time = time;
            this.modalOpen = true;
        },

        resetForm() {
            this.form = {
                professional_id: '',
                patient_id: '',
                appointment_date: this.getTodayDate(),
                appointment_time: '',
                duration: 30,
                office_id: '',
                notes: '',
                estimated_amount: '',
                status: 'scheduled',
                is_between_turn: false,
                // Campos de pago
                pay_now: false,
                payment_type: 'single',
                payment_amount: '',
                payment_method: '',
                payment_concept: '',
                // Campos de paquete
                package_sessions: '',
                session_price: ''
            };
        },

        getTodayDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        // Retorna { maxMins, time } con el límite para no superponer al siguiente turno
        // del mismo profesional ese día, o null si no hay restricción.
        get nextAppointmentConstraint() {
            if (!this.form.appointment_date || !this.form.appointment_time || !this.form.professional_id) return null;
            const dayApps = this.allAppointments[this.form.appointment_date] || [];
            const [fh, fm] = this.form.appointment_time.split(':').map(Number);
            const currentMins = fh * 60 + fm;
            let nextApt = null;
            let nextMins = Infinity;
            for (const apt of dayApps) {
                if (this.editingAppointment && apt.id === this.editingAppointment.id) continue;
                if (String(apt.professional.id) !== String(this.form.professional_id)) continue;
                const d = new Date(apt.appointment_date);
                const aptMins = d.getHours() * 60 + d.getMinutes();
                if (aptMins > currentMins && aptMins < nextMins) {
                    nextMins = aptMins;
                    nextApt = apt;
                }
            }
            if (!nextApt) return null;
            return { maxMins: nextMins - currentMins, time: this.formatTime(nextApt.appointment_date) };
        },

        validateDateTime() {
            this.pastTimeError = '';

            if (this.form.appointment_date && this.form.appointment_time) {
                const appointmentDateTime = new Date(this.form.appointment_date + 'T' + this.form.appointment_time);
                const now = new Date();

                if (appointmentDateTime <= now) {
                    this.pastTimeError = 'No se pueden programar turnos en fechas y horarios pasados.';
                    return false;
                }
            }

            // Si la duración actual supera el tiempo disponible hasta el próximo turno,
            // reducirla automáticamente a la mayor opción permitida.
            const constraint = this.nextAppointmentConstraint;
            if (constraint && parseInt(this.form.duration) > constraint.maxMins) {
                const best = [...this.durationOptions].reverse().find(o => o.value <= constraint.maxMins);
                this.form.duration = best ? best.value : 5;
            }

            return true;
        },

        async submitForm() {
            // Validate datetime before submitting
            if (!this.validateDateTime()) {
                return;
            }

            // Validar que la duración no superponga con el siguiente turno
            const constraint = this.nextAppointmentConstraint;
            if (constraint && parseInt(this.form.duration) > constraint.maxMins) {
                this.showNotification(
                    `La duración elegida (${this.form.duration} min) superaría el siguiente turno de las ${constraint.time}. Máximo disponible: ${constraint.maxMins} min.`,
                    'error'
                );
                return;
            }

            this.loading = true;

            try {
                const url = this.editingAppointment ?
                    `/appointments/${this.editingAppointment.id}` :
                    '/appointments';
                const method = this.editingAppointment ? 'PUT' : 'POST';

                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    let value = this.form[key];

                    // Convertir booleanos a enteros para evitar problemas con FormData
                    if (typeof value === 'boolean') {
                        value = value ? 1 : 0;
                    }

                    if (value !== '' && value !== null && value !== undefined) {
                        formData.append(key, value);
                    } else if (key === 'status' || key === 'notes' || key === 'office_id' || key === 'estimated_amount') {
                        formData.append(key, value || '');
                    }
                });

                if (this.editingAppointment) {
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
                    this.showNotification(result.message, 'success');
                    // Reload page to show changes
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    if (response.status === 422 && result.errors) {
                        this.setErrors(result.errors);
                        this.showNotification('Por favor corregí los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al guardar el turno', 'error');
                    }
                }
            } catch (error) {
                this.showNotification('Error al guardar el turno', 'error');
            } finally {
                this.loading = false;
            }
        },

        openDayModal(date, professionalId) {
            this.selectedDayDate = date;
            this.selectedProfessionalId = professionalId;

            // Find professional name
            const professional = this.professionals.find(p => p.id == professionalId);
            this.selectedProfessionalName = professional ? professional.first_name + ' ' + professional.last_name : '';

            // Get appointments for this day
            this.dayAppointments = this.allAppointments[date] || [];

            // Calcular horario del día para el timeline
            const [y, m, d] = date.split('-');
            const dateObj = new Date(+y, +m - 1, +d);
            const jsDay = dateObj.getDay();          // 0=Dom, 1=Lun…6=Sab
            const dayKey = jsDay === 0 ? 7 : jsDay; // → 1=Lun…7=Dom (igual a Laravel)
            const sched = this.schedules[dayKey];

            if (sched) {
                const [sh, sm] = sched.start_time.split(':').map(Number);
                const [eh, em] = sched.end_time.split(':').map(Number);
                this.daySchedule = {
                    startMins: sh * 60 + sm,
                    endMins:   eh * 60 + em,
                    totalMins: (eh * 60 + em) - (sh * 60 + sm),
                    startTime: sched.start_time,
                    endTime:   sched.end_time,
                };
            } else {
                this.daySchedule = null;
            }

            this.dayModalOpen = true;
        },

        openEditModalById(appointmentId) {
            // Find appointment in all appointments data
            let appointment = null;
            Object.values(this.allAppointments).forEach(dayApps => {
                if (Array.isArray(dayApps)) {
                    const found = dayApps.find(app => app.id == appointmentId);
                    if (found) {
                        appointment = found;
                    }
                }
            });

            if (appointment) {
                this.openEditModal(appointment);
            }
        },

        openEditModal(appointment) {
            this.editingAppointment = appointment;
            this.clearAllErrors();
            const appointmentDate = new Date(appointment.appointment_date);

            this.form = {
                professional_id: appointment.professional.id.toString(),
                patient_id: appointment.patient.id.toString(),
                appointment_date: appointmentDate.toISOString().split('T')[0],
                appointment_time: appointmentDate.toTimeString().slice(0, 5),
                duration: appointment.duration,
                office_id: appointment.office?.id.toString() || '',
                notes: appointment.notes || '',
                estimated_amount: appointment.estimated_amount || '',
                status: appointment.status || 'scheduled',
                is_between_turn: appointment.is_between_turn || false
            };
            this.modalOpen = true;
        },

        // Horas del timeline (marcas cada 60 min)
        get timelineHours() {
            if (!this.daySchedule) return [];
            const hours = [];
            const startH = Math.floor(this.daySchedule.startMins / 60);
            const endH   = Math.ceil(this.daySchedule.endMins / 60);
            for (let h = startH; h <= endH; h++) {
                hours.push({
                    label: String(h).padStart(2, '0') + ':00',
                    topPx: (h * 60 - this.daySchedule.startMins) * this.pxPerMin,
                });
            }
            return hours;
        },

        // Marcas de media hora (solo líneas, sin label)
        get timelineHalfHours() {
            if (!this.daySchedule) return [];
            const halves = [];
            const startH = Math.floor(this.daySchedule.startMins / 60);
            const endH   = Math.ceil(this.daySchedule.endMins / 60);
            for (let h = startH; h < endH; h++) {
                const topPx = (h * 60 + 30 - this.daySchedule.startMins) * this.pxPerMin;
                if (topPx > 0 && topPx < this.daySchedule.totalMins * this.pxPerMin) {
                    halves.push({ topPx });
                }
            }
            return halves;
        },

        // Layout unificado con posicionamiento TEMPORAL PURO.
        // Appointments y slots libres usan el mismo sistema de coordenadas que
        // las marcas de hora → el grid siempre está alineado con el contenido.
        get timelineLayout() {
            if (!this.daySchedule) return { items: [] };
            const { startMins, endMins } = this.daySchedule;
            const px = (mins) => mins * this.pxPerMin;
            const isPast = this.isDayInPast();
            const SLOT = 30;
            const MIN_FREE = 5;

            // Ordenar turnos y calcular posiciones basadas en tiempo
            const sortedApts = [...this.dayAppointments]
                .map(apt => {
                    const d = new Date(apt.appointment_date);
                    const s = d.getHours() * 60 + d.getMinutes();
                    const dur = apt.duration > 0 ? apt.duration : 0;
                    return { apt, sMins: s, dur };
                })
                .sort((a, b) => a.sMins - b.sMins);

            const items = [];

            // Bloques de turnos: topPx y heightPx derivados del tiempo real
            for (const { apt, sMins, dur } of sortedApts) {
                items.push({
                    type: 'appointment',
                    topPx: px(sMins - startMins),
                    heightPx: Math.max(px(dur), 24), // 24px mínimo solo para urgencias/turnos <8min
                    apt,
                });
            }

            // Slots libres: tiempo real → mismas coordenadas que el grid
            if (!isPast) {
                // Fusionar intervalos ocupados
                const busy = sortedApts
                    .map(({ sMins, dur }) => [sMins, sMins + dur])
                    .reduce((acc, [s, e]) => {
                        if (acc.length && s <= acc[acc.length - 1][1]) {
                            acc[acc.length - 1][1] = Math.max(acc[acc.length - 1][1], e);
                        } else {
                            acc.push([s, e]);
                        }
                        return acc;
                    }, []);

                // Encontrar segmentos libres y dividirlos en chunks de 30 min
                const freeSegs = [];
                let prev = startMins;
                for (const [s, e] of busy) {
                    if (s > prev) freeSegs.push([prev, s]);
                    prev = Math.max(prev, e);
                }
                if (prev < endMins) freeSegs.push([prev, endMins]);

                for (const [segStart, segEnd] of freeSegs) {
                    let cur = segStart;
                    while (cur < segEnd) {
                        const nextB    = (Math.floor(cur / SLOT) + 1) * SLOT;
                        const chunkEnd = Math.min(nextB, segEnd);
                        const chunkMins = chunkEnd - cur;
                        if (chunkMins >= MIN_FREE) {
                            const h = Math.floor(cur / 60);
                            const m = cur % 60;
                            items.push({
                                type: 'free',
                                topPx: px(cur - startMins),
                                heightPx: Math.max(px(chunkMins) - 2, 20),
                                startMins: cur,
                                label: String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'),
                            });
                        }
                        cur = chunkEnd;
                    }
                }
            }

            return { items };
        },

        get timelineHeightPx() {
            return this.daySchedule ? this.daySchedule.totalMins * this.pxPerMin : 0;
        },

        appointmentBlockClass(apt) {
            if (apt.duration === 0) {
                return 'bg-red-500 border-l-4 border-red-700 text-white ring-1 ring-red-300';
            }
            if (apt.is_between_turn) {
                return 'bg-orange-400 border-l-4 border-orange-600 text-white';
            }
            const map = {
                scheduled: 'bg-blue-500 border-l-4 border-blue-700 text-white',
                attended:  'bg-green-500 border-l-4 border-green-700 text-white',
                absent:    'bg-orange-500 border-l-4 border-orange-700 text-white',
            };
            return map[apt.status] || 'bg-gray-400 border-l-4 border-gray-600 text-white';
        },

        formatDateSpanish(dateString) {
            // Parse como fecha local para evitar problemas de timezone
            const [year, month, day] = dateString.split('-');
            const date = new Date(year, month - 1, day);
            return date.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            });
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        },

        getStatusBadgeClass(status) {
            const classes = {
                scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                attended: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                absent: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        },

        getStatusText(status) {
            const texts = {
                scheduled: 'Programado',
                attended: 'Atendido',
                cancelled: 'Cancelado',
                absent: 'Ausente'
            };
            return texts[status] || status;
        },

        isDayInPast() {
            if (!this.selectedDayDate) return false;
            // Comparar solo las fechas como strings para evitar problemas de timezone
            const today = this.getTodayDate();
            return this.selectedDayDate < today;
        },

        isAppointmentInPast(appointment) {
            if (!appointment || !appointment.appointment_date) return false;
            const appointmentDate = new Date(appointment.appointment_date);
            const now = new Date();
            return appointmentDate < now;
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
        }
    }))
});

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar Select2 para el selector de profesionales en la agenda
    if ($('#agenda-professional-select').length) {
        const agendaProfessionalSelect = $('#agenda-professional-select').select2({
            placeholder: 'Buscar profesional...',
            allowClear: true,
            width: '500px',
            dropdownAutoWidth: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Submit form cuando cambia la selección
        agendaProfessionalSelect.on('change', function() {
            document.getElementById('professional-form').submit();
        });

        // Autofocus en el campo de búsqueda
        agendaProfessionalSelect.on('select2:open', function() {
            document.querySelector('.select2-search__field').focus();
        });
    }

    // Variables para los selects del modal
    let professionalSelect = null;
    let patientSelect = null;
    let modalCheckInterval = null;

    // Función para verificar si el modal está abierto
    function checkModalState() {
        const modal = document.querySelector('[x-show="modalOpen"]');

        if (modal && modal.style.display !== 'none' && !modal.hasAttribute('hidden')) {
            // Modal está abierto
            if (!professionalSelect || !patientSelect) {
                setTimeout(() => {
                    initializeSelect2();
                }, 150);
            }
        } else {
            // Modal está cerrado
            destroySelect2();
        }
    }

    // Verificar el estado del modal cada 300ms
    modalCheckInterval = setInterval(checkModalState, 300);

    function initializeSelect2() {
        // Inicializar Select2 para Profesional en el modal
        if (!professionalSelect && $('#professional-select').length) {
            professionalSelect = $('#professional-select').select2({
                placeholder: 'Buscar profesional...',
                allowClear: false,
                width: '100%',
                dropdownParent: $('.bg-white.dark\\:bg-gray-800.rounded-lg.shadow-xl').first(),
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Sincronizar con Alpine.js - Actualizar directamente el modelo
            professionalSelect.on('change', function(e) {
                const selectedValue = $(this).val();
                // Buscar el componente Alpine.js y actualizar el form
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentModal()"]'));
                if (alpineComponent) {
                    alpineComponent.form.professional_id = selectedValue;
                }
            });

            // Autofocus en el campo de búsqueda
            professionalSelect.on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        }

        // Función para normalizar texto (quitar acentos)
        function normalizeText(text) {
            return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        // Inicializar Select2 para Paciente con búsqueda por DNI en el modal
        if (!patientSelect && $('#patient-select').length) {
            patientSelect = $('#patient-select').select2({
                placeholder: 'Buscar paciente por nombre o DNI...',
                allowClear: false,
                width: '100%',
                dropdownParent: $('.bg-white.dark\\:bg-gray-800.rounded-lg.shadow-xl').first(),
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todo
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // No buscar en el placeholder vacío
                    if (!data.id) {
                        return null;
                    }

                    // Normalizar el término de búsqueda (quitar acentos y lowercase)
                    const searchTerm = normalizeText(params.term);
                    const text = normalizeText(data.text || '');

                    // Obtener atributos data del elemento option y normalizarlos
                    const $option = $(data.element);
                    const dni = normalizeText($option.attr('data-dni') || '');
                    const firstName = normalizeText($option.attr('data-first-name') || '');
                    const lastName = normalizeText($option.attr('data-last-name') || '');

                    // Buscar en texto completo, DNI, nombre o apellido
                    if (text.indexOf(searchTerm) > -1 ||
                        dni.indexOf(searchTerm) > -1 ||
                        firstName.indexOf(searchTerm) > -1 ||
                        lastName.indexOf(searchTerm) > -1) {
                        return data;
                    }

                    return null;
                }
            });

            // Sincronizar con Alpine.js - Actualizar directamente el modelo
            patientSelect.on('change', function(e) {
                const selectedValue = $(this).val();
                // Buscar el componente Alpine.js y actualizar el form
                const alpineComponent = Alpine.$data(document.querySelector('[x-data="appointmentModal()"]'));
                if (alpineComponent) {
                    alpineComponent.form.patient_id = selectedValue;
                }
            });

            // Autofocus en el campo de búsqueda
            patientSelect.on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        }
    }

    function destroySelect2() {
        if (professionalSelect) {
            professionalSelect.select2('destroy');
            professionalSelect = null;
        }
        if (patientSelect) {
            patientSelect.select2('destroy');
            patientSelect = null;
        }
    }
});

// Componente Alpine.js para el modal de pacientes
function patientModal() {
    return {
        modalOpen: false,
        editingPatient: null,
        loading: false,

        form: {
            first_name: '',
            last_name: '',
            dni: '',
            birth_date: '',
            email: '',
            phone: '',
            address: '',
            health_insurance: '',
            health_insurance_number: '',
            titular_obra_social: '',
            plan_obra_social: ''
        },

        init() {
            // Escuchar evento para abrir el modal
            this.$watch('modalOpen', (value) => {
                if (!value) {
                    this.editingPatient = null;
                    this.resetForm();
                }
            });

            // Escuchar evento personalizado
            window.addEventListener('open-patient-modal', () => {
                this.openModal();
            });

            // Seleccionar paciente recién creado si existe
            const newPatientId = sessionStorage.getItem('newPatientId');
            if (newPatientId) {
                setTimeout(() => {
                    $('#patient-select').val(newPatientId).trigger('change');
                    sessionStorage.removeItem('newPatientId');
                }, 1000);
            }
        },

        openModal() {
            this.resetForm();
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
                health_insurance_number: '',
                titular_obra_social: '',
                plan_obra_social: ''
            };
        },

        async submitForm() {
            this.loading = true;

            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('/patients', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.modalOpen = false;

                    // Mostrar notificación de éxito
                    window.showToast('Paciente creado exitosamente. La página se recargará para actualizar la lista.', 'success');

                    // Guardar el ID del nuevo paciente para seleccionarlo después de recargar
                    if (result.patient && result.patient.id) {
                        sessionStorage.setItem('newPatientId', result.patient.id);
                    }

                    // Recargar la página
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    if (response.status === 422 && result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        window.showToast('Errores de validación: ' + errorMessages, 'error');
                    } else {
                        window.showToast(result.message || 'Error al guardar el paciente', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                window.showToast('Error al guardar el paciente', 'error');
            } finally {
                this.loading = false;
            }
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
        }
    }
}
</script>
@endpush
