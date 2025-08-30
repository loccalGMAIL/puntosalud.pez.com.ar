// Dashboard functionality
class DashboardManager {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.paymentModal = null;
        this.init();
    }

    init() {
        this.setupGlobalPaymentModal();
    }

    setupGlobalPaymentModal() {
        // Make paymentModal available globally for Alpine
        window.globalPaymentModal = null;
    }

    // API calls with consistent error handling
    async makeRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Error en la operación');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Show notifications (can be replaced with toast library later)
    showNotification(message, type = 'info') {
        // For now use alert, but this can be improved with a toast library
        if (type === 'error') {
            alert(`❌ ${message}`);
        } else if (type === 'success') {
            alert(`✅ ${message}`);
        } else {
            alert(message);
        }
    }

    // Reload page with optional delay
    reloadPage(delay = 0) {
        if (delay > 0) {
            setTimeout(() => location.reload(), delay);
        } else {
            location.reload();
        }
    }
}

// Alpine.js components
document.addEventListener('alpine:init', () => {
    const dashboardManager = new DashboardManager();

    // Appointment Actions Component
    Alpine.data('appointmentActions', (appointmentId, estimatedAmount = 0) => ({
        loading: false,

        async markAttended() {
            if (this.loading) return;
            
            this.loading = true;
            
            try {
                await dashboardManager.makeRequest(`/dashboard/appointments/${appointmentId}/mark-attended`, {
                    method: 'POST'
                });
                
                dashboardManager.showNotification('Turno marcado como atendido exitosamente', 'success');
                dashboardManager.reloadPage(500);
            } catch (error) {
                dashboardManager.showNotification(error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        markCompletedAndPaid() {
            if (window.globalPaymentModal) {
                window.globalPaymentModal.showModal(appointmentId, estimatedAmount);
            }
        },

        async markAbsent() {
            if (!confirm('¿Está seguro de marcar este turno como ausente?')) {
                return;
            }
            
            if (this.loading) return;
            this.loading = true;
            
            try {
                await dashboardManager.makeRequest(`/dashboard/appointments/${appointmentId}/mark-absent`, {
                    method: 'POST'
                });
                
                dashboardManager.showNotification('Turno marcado como ausente', 'success');
                dashboardManager.reloadPage(500);
            } catch (error) {
                dashboardManager.showNotification(error.message, 'error');
            } finally {
                this.loading = false;
            }
        }
    }));

    // Payment Modal Component
    Alpine.data('paymentModal', () => ({
        show: false,
        loading: false,
        currentAppointmentId: null,
        paymentForm: {
            final_amount: '',
            payment_method: '',
            concept: ''
        },

        init() {
            window.globalPaymentModal = this;
        },

        showModal(appointmentId, estimatedAmount = 0) {
            this.currentAppointmentId = appointmentId;
            this.show = true;
            // Reset form with estimated amount
            this.paymentForm = {
                final_amount: estimatedAmount || '',
                payment_method: '',
                concept: ''
            };
        },

        hide() {
            this.show = false;
            this.currentAppointmentId = null;
            this.loading = false;
        },

        async submitPayment() {
            if (this.loading) return;
            
            // Basic validation
            if (!this.paymentForm.final_amount || !this.paymentForm.payment_method) {
                dashboardManager.showNotification('Por favor complete todos los campos requeridos', 'error');
                return;
            }

            this.loading = true;

            try {
                const result = await dashboardManager.makeRequest(
                    `/dashboard/appointments/${this.currentAppointmentId}/mark-completed-paid`,
                    {
                        method: 'POST',
                        body: JSON.stringify(this.paymentForm)
                    }
                );

                this.hide();
                dashboardManager.showNotification(
                    `Turno finalizado y cobrado. Recibo #${result.receipt_number}`,
                    'success'
                );
                dashboardManager.reloadPage(500);
            } catch (error) {
                dashboardManager.showNotification(error.message, 'error');
                this.loading = false;
            }
        }
    }));
});

// Export for potential use in other modules
export default DashboardManager;