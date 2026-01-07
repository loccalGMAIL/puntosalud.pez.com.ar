@extends('layouts.app')

@section('title', 'Ingreso Manual - ' . config('app.name'))
@section('mobileTitle', 'Ingreso Manual')

@section('content')
<div class="p-6" x-data="incomeForm()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('cash.daily') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Caja del Día</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Ingreso Manual</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registrar Ingreso Manual</h1>
            <p class="text-gray-600 dark:text-gray-400">Registre entradas de dinero no relacionadas con turnos</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('cash.daily') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Volver a Caja
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submitIncome()" class="space-y-6">
        <!-- Información Básica del Ingreso -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                    Información del Ingreso
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Monto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Monto *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-500 dark:text-gray-400">$</span>
                            <input x-model="form.amount"
                                   type="number"
                                   step="0.01"
                                   min="0.01"
                                   class="w-full pl-8 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Categoría del Ingreso *
                        </label>
                        <select x-model="form.category"
                                @change="handleCategoryChange()"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="">Seleccionar categoría</option>
                            @foreach($incomeCategories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Método de Pago *
                        </label>
                        <select x-model="form.payment_method"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
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
                    <div >
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción del Ingreso *
                        </label>
                        <input x-model="form.description"
                               type="text"
                               maxlength="255"
                               class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Descripción del ingreso..."
                               required>
                    </div>

                    <!-- Profesional (solo si es pago módulo profesional) -->
                    <div x-show="form.category === 'professional_module_payment'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Profesional *
                        </label>
                        @if(count($professionals) > 0)
                            <select x-model="form.professional_id"
                                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                                    :required="form.category === 'professional_module_payment'">
                                <option value="">Seleccionar profesional</option>
                                @foreach($professionals as $prof)
                                <option value="{{ $prof->id }}">{{ $prof->full_name }} - {{ $prof->specialty->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Este ingreso se asociará al profesional seleccionado
                            </p>
                        @else
                            <div class="w-full px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No hay profesionales disponibles</h4>
                                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                            Solo se pueden registrar pagos de módulo para profesionales que tengan turnos programados para hoy ({{ now()->format('d/m/Y') }})
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>


                </div>

                <!-- Notas Adicionales -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Notas Adicionales
                    </label>
                    <textarea x-model="form.notes"
                              rows="3"
                              maxlength="500"
                              placeholder="Información adicional sobre el ingreso (opcional)..."
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="`${form.notes.length}/500 caracteres`"></div>
                </div>
            </div>
        </div>

        <!-- Adjuntar Comprobante -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                    </svg>
                    Comprobante (Opcional)
                </h2>

                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6">
                    <input type="file"
                           @change="handleFileUpload"
                           accept=".jpg,.jpeg,.png,.pdf"
                           class="hidden"
                           id="receipt-file">

                    <div x-show="!selectedFile" class="text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <div class="text-gray-600 dark:text-gray-400 mb-2">
                            <label for="receipt-file" class="cursor-pointer text-blue-600 hover:text-blue-500 font-medium">
                                Hacer clic para subir
                            </label>
                            <span> o arrastrar archivo aquí</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            JPG, JPEG, PNG, PDF hasta 2MB
                        </p>
                    </div>

                    <div x-show="selectedFile" class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0-1.125-.504-1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedFile?.name"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatFileSize(selectedFile?.size)"></div>
                            </div>
                        </div>
                        <button type="button"
                                @click="removeFile()"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('cash.daily') }}"
               class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    :disabled="loading || !isFormValid()"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:bg-green-400 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:cursor-not-allowed">
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

        form: {
            amount: '',
            category: '',
            payment_method: 'cash', // Valor por defecto
            professional_id: '',
            description: '',
            notes: ''
        },

        categories: @json($incomeCategories),
        hasProfessionals: {{ count($professionals) > 0 ? 'true' : 'false' }},

        init() {
            // Leer parámetros de URL para precargar el formulario
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('amount')) {
                this.form.amount = urlParams.get('amount');
            }
            if (urlParams.has('category')) {
                this.form.category = urlParams.get('category');
            }
            if (urlParams.has('payment_method')) {
                this.form.payment_method = urlParams.get('payment_method');
            }
            if (urlParams.has('professional_id')) {
                this.form.professional_id = urlParams.get('professional_id');
            }
            if (urlParams.has('description')) {
                this.form.description = decodeURIComponent(urlParams.get('description'));
            }
            if (urlParams.has('notes')) {
                this.form.notes = decodeURIComponent(urlParams.get('notes'));
            }

            // Si hay una liquidación precargada, mostrar un mensaje
            if (urlParams.has('from_liquidation')) {
                setTimeout(() => {
                    const message = '💰 Liquidación con monto negativo detectada.\n\nPor favor registre el ingreso del profesional al centro.';
                    if (typeof SystemModal !== 'undefined') {
                        SystemModal.show('info', 'Registrar Ingreso', message, 'Entendido');
                    } else {
                        alert(message);
                    }
                }, 500);
            }
        },

        handleCategoryChange() {
            // Limpiar professional_id si no es pago módulo profesional
            if (this.form.category !== 'professional_module_payment') {
                this.form.professional_id = '';
            }
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                // Validar tamaño (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 2MB permitido.');
                    return;
                }

                // Validar tipo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Tipo de archivo no válido. Solo JPG, PNG y PDF.');
                    return;
                }

                this.selectedFile = file;
            }
        },

        removeFile() {
            this.selectedFile = null;
            document.getElementById('receipt-file').value = '';
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
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

            // Si es pago módulo profesional, validar que haya profesionales disponibles y uno seleccionado
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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Preguntar si desea imprimir el recibo
                    if (result.payment_id) {
                        const printReceipt = await SystemModal.confirm(
                            'Imprimir recibo',
                            '¿Desea imprimir el recibo ahora?',
                            'Sí, imprimir',
                            'No'
                        );

                        if (printReceipt) {
                            window.open(`/cash/income-receipt/${result.payment_id}?print=1`, '_blank');
                        }
                    }

                    // Redirigir después de un momento
                    setTimeout(() => {
                        window.location.href = '/cash/daily';
                    }, 500);
                } else {
                    alert(result.message || 'Error al registrar el ingreso');
                }
            } catch (error) {
                alert('Error al registrar el ingreso');
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
