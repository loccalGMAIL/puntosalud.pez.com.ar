@extends('layouts.app')

@section('title', 'Ingreso Manual - ' . config('app.name'))
@section('mobileTitle', 'Ingreso Manual')

@section('content')
<div class="p-4 sm:p-6" x-data="incomeForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('cash.daily') }}" class="hover:text-gray-700">Caja del Día</a>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Ingreso Manual</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">Registrar Ingreso Manual</h1>
        </div>

        <a href="{{ route('cash.daily') }}"
           class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 self-start sm:self-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Volver a Caja
        </a>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submitIncome()" class="space-y-3">

        <!-- Información Básica del Ingreso -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Monto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input x-model="form.amount"
                                   type="number"
                                   step="0.01"
                                   min="0.01"
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                        <select x-model="form.category"
                                @change="handleCategoryChange()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"
                                required>
                            <option value="">Seleccionar categoría</option>
                            @foreach($incomeCategories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago *</label>
                        <select x-model="form.payment_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"
                                required>
                            <option value="">Seleccionar método</option>
                            <option value="cash">💵 Efectivo</option>
                            <option value="transfer">🏦 Transferencia</option>
                            <option value="debit_card">💳 Tarjeta de Débito</option>
                            <option value="credit_card">💳 Tarjeta de Crédito</option>
                            <option value="qr">📱 QR</option>
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
                        <input x-model="form.description"
                               type="text"
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"
                               placeholder="Descripción del ingreso..."
                               required>
                    </div>

                    <!-- Profesional (solo si es pago módulo profesional) -->
                    <div x-show="form.category === 'professional_module_payment'" x-cloak class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profesional *</label>
                        @if(count($professionals) > 0)
                            <select x-model="form.professional_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"
                                    :required="form.category === 'professional_module_payment'">
                                <option value="">Seleccionar profesional</option>
                                @foreach($professionals as $prof)
                                <option value="{{ $prof->id }}">{{ $prof->full_name }} - {{ $prof->specialty->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Este ingreso se asociará al profesional seleccionado</p>
                        @else
                            <div class="w-full px-3 py-2.5 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start gap-2">
                                <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">No hay profesionales disponibles</p>
                                    <p class="text-xs text-yellow-700 mt-0.5">Solo para profesionales con turnos programados hoy ({{ now()->format('d/m/Y') }})</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Acordeón: Notas y Comprobante -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Cabecera del acordeón -->
            <button type="button"
                    @click="extrasOpen = !extrasOpen"
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors duration-150">
                <span class="flex items-center gap-2 text-sm font-medium text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Notas y comprobante
                    <span x-show="selectedFile || form.notes.length > 0"
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                        con datos
                    </span>
                </span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                     :class="extrasOpen ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Contenido del acordeón -->
            <div x-show="extrasOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="border-t border-gray-100 px-4 py-4 space-y-4">

                <!-- Notas Adicionales -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales</label>
                    <textarea x-model="form.notes"
                              rows="2"
                              maxlength="500"
                              placeholder="Información adicional sobre el ingreso (opcional)..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 resize-none text-sm"></textarea>
                    <div class="text-xs text-gray-400 mt-0.5 text-right" x-text="`${form.notes.length}/500`"></div>
                </div>

                <!-- Comprobante -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comprobante (opcional)</label>
                    <input type="file"
                           @change="handleFileUpload"
                           accept=".jpg,.jpeg,.png,.pdf"
                           class="hidden"
                           id="receipt-file">

                    <!-- Sin archivo -->
                    <div x-show="!selectedFile"
                         class="border-2 border-dashed border-gray-200 rounded-lg px-4 py-5 text-center hover:border-gray-300 transition-colors">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <label for="receipt-file" class="cursor-pointer text-sm text-blue-600 hover:text-blue-500 font-medium">
                            Seleccionar archivo
                        </label>
                        <span class="text-sm text-gray-500"> o arrastrarlo aquí</span>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — máx. 2 MB</p>
                    </div>

                    <!-- Con archivo -->
                    <div x-show="selectedFile"
                         class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2.5">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900" x-text="selectedFile?.name"></div>
                                <div class="text-xs text-gray-500" x-text="formatFileSize(selectedFile?.size)"></div>
                            </div>
                        </div>
                        <button type="button"
                                @click="removeFile()"
                                class="text-red-500 hover:text-red-700 p-1 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-1">
            <a href="{{ route('cash.daily') }}"
               class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    :disabled="loading || !isFormValid()"
                    class="px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:bg-green-400 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:cursor-not-allowed">
                <span x-show="!loading" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Registrar Ingreso
                </span>
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
function incomeForm() {
    return {
        loading: false,
        selectedFile: null,
        extrasOpen: false,

        form: {
            amount: '',
            category: '',
            payment_method: 'cash',
            professional_id: '',
            description: '',
            notes: ''
        },

        categories: @json($incomeCategories),
        hasProfessionals: {{ count($professionals) > 0 ? 'true' : 'false' }},

        init() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('amount')) this.form.amount = urlParams.get('amount');
            if (urlParams.has('category')) this.form.category = urlParams.get('category');
            if (urlParams.has('payment_method')) this.form.payment_method = urlParams.get('payment_method');
            if (urlParams.has('professional_id')) this.form.professional_id = urlParams.get('professional_id');
            if (urlParams.has('description')) this.form.description = decodeURIComponent(urlParams.get('description'));
            if (urlParams.has('notes')) {
                this.form.notes = decodeURIComponent(urlParams.get('notes'));
                if (this.form.notes) this.extrasOpen = true;
            }

            if (urlParams.has('from_liquidation')) {
                setTimeout(() => {
                    const message = '💰 Liquidación con monto negativo detectada.\n\nPor favor registre el ingreso del profesional al centro.';
                    if (typeof SystemModal !== 'undefined') {
                        SystemModal.show('info', 'Registrar Ingreso', message, 'Entendido');
                    } else {
                        window.showToast(message, 'info');
                    }
                }, 500);
            }
        },

        handleCategoryChange() {
            if (this.form.category !== 'professional_module_payment') {
                this.form.professional_id = '';
            }
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                window.showToast('El archivo es muy grande. Máximo 2MB permitido.', 'warning');
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                window.showToast('Tipo de archivo no válido. Solo JPG, PNG y PDF.', 'warning');
                return;
            }

            this.selectedFile = file;
        },

        removeFile() {
            this.selectedFile = null;
            document.getElementById('receipt-file').value = '';
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        getCategoryLabel() {
            return this.categories[this.form.category] || 'Sin categoría';
        },

        formatNumber(num) {
            return new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        },

        isFormValid() {
            const baseValid = this.form.amount &&
                   this.form.category &&
                   this.form.payment_method &&
                   this.form.description.trim();

            if (this.form.category === 'professional_module_payment') {
                return baseValid && this.hasProfessionals && this.form.professional_id;
            }

            return baseValid;
        },

        async submitIncome() {
            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('amount', this.form.amount);
                formData.append('category', this.form.category);
                formData.append('payment_method', this.form.payment_method);
                formData.append('description', this.form.description);
                formData.append('notes', this.form.notes);

                if (this.form.professional_id) {
                    formData.append('professional_id', this.form.professional_id);
                }

                if (this.selectedFile) {
                    formData.append('receipt_file', this.selectedFile);
                }

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('/cash/manual-income', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (response.status === 419) {
                    window.showToast(result.message || 'Tu sesión ha expirado. Redirigiendo...', 'warning');
                    setTimeout(() => { window.location.href = result.redirect || '/login'; }, 1500);
                } else if (response.ok && result.success) {
                    if (result.payment_id) {
                        const action = await window.askReceiptAction({
                            paymentId: result.payment_id,
                            kind: 'manual_income',
                            hasPatientPhone: false
                        });

                        if (action === 'print') {
                            window.open(`/cash/income-receipt/${result.payment_id}?print=1`, '_blank');
                        }

                        if (action === 'whatsapp') {
                            await window.shareIncomeReceiptByWhatsApp(result.payment_id);
                        }
                    }

                    setTimeout(() => { window.location.href = '/cash/daily'; }, 500);
                } else {
                    window.showToast(result.message || 'Error al registrar el ingreso', 'error');
                }
            } catch (error) {
                window.showToast('Error al registrar el ingreso', 'error');
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

<!-- Modal de Sistema -->
<x-system-modal />

@endsection
