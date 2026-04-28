@extends('layouts.app')

@section('title', 'Gastos Externos - ' . config('app.name'))
@section('mobileTitle', 'Gastos Externos')

@section('content')
<div class="p-6" x-data="expensesPage()" x-init="init()" data-edit-expense="{{ e(json_encode($editExpense)) }}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Gastos Externos</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gastos Externos</h1>
            <p class="text-gray-600 dark:text-gray-400">Sueldos, impuestos, alquiler y otros gastos fuera de la caja diaria.</p>
        </div>

        <div class="flex gap-3">
            <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo gasto
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros</h2>
        <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha desde</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha hasta</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                <select name="movement_type_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Todos</option>
                    @foreach($expenseTypes as $type)
                        <option value="{{ $type->id }}" {{ (string)$movementTypeId === (string)$type->id ? 'selected' : '' }}>
                            {{ $type->icon }} {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors">
                    Generar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <div class="flex items-baseline justify-between gap-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Registros</h2>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $expenses->total() }} en total</div>
            </div>

            @if($expenses->count() > 0)
                <div class="overflow-x-auto hidden md:block">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Fecha</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Tipo</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Descripción</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Monto</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Registrado</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($expenses as $expense)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $expense->expense_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ $expense->movementType?->icon }} {{ $expense->movementType?->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                        <div class="font-medium">{{ $expense->description }}</div>
                                        @if($expense->notes)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $expense->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right font-semibold text-red-600 dark:text-red-400">
                                        -${{ number_format($expense->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $expense->creator?->name ?? '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if($expense->receipt_path)
                                                <a href="{{ url('/storage/' . $expense->receipt_path) }}" target="_blank"
                                                   class="p-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
                                                   title="Ver comprobante">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v2.625a3.375 3.375 0 01-3.375 3.375h-8.25A3.375 3.375 0 014.5 16.875V14.25m7.5-10.5v11.25m0 0l-3-3m3 3l3-3" />
                                                    </svg>
                                                </a>
                                            @endif

                                            <button type="button" @click="openEditModal(@js($expense))"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg"
                                                    title="Editar">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <button type="button" @click="confirmDeleteFromRow({{ $expense->id }})"
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
                                                    title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile cards -->
                <div class="space-y-3 md:hidden">
                    @foreach($expenses as $expense)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $expense->expense_date->format('d/m/Y') }}</div>
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $expense->movementType?->icon }} {{ $expense->movementType?->name ?? '—' }}</div>
                                    <div class="mt-2 text-sm text-gray-900 dark:text-white">{{ $expense->description }}</div>
                                    @if($expense->notes)
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $expense->notes }}</div>
                                    @endif

                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-red-600 dark:text-red-400">-${{ number_format($expense->amount, 2, ',', '.') }}</div>
                                    <div class="mt-2">
                                        <div class="flex justify-end gap-2">
                                            @if($expense->receipt_path)
                                                <a href="{{ url('/storage/' . $expense->receipt_path) }}" target="_blank"
                                                   class="p-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
                                                   title="Ver comprobante">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v2.625a3.375 3.375 0 01-3.375 3.375h-8.25A3.375 3.375 0 014.5 16.875V14.25m7.5-10.5v11.25m0 0l-3-3m3 3l3-3" />
                                                    </svg>
                                                </a>
                                            @endif

                                            <button type="button" @click="openEditModal(@js($expense))"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg"
                                                    title="Editar">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <button type="button" @click="confirmDeleteFromRow({{ $expense->id }})"
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
                                                    title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $expenses->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 12a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V12zm-12 0h.008v.008H6V12z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 font-medium">No hay gastos externos en el período seleccionado.</p>
                        <a href="{{ route('expenses.index') }}" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            Limpiar filtros
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('expenses.modal')

    <x-system-modal />

</div>

<script>
const EXPENSES_DEFAULT_DATE = "{{ now()->format('Y-m-d') }}";

function expensesPage() {
    return {
        modalOpen: false,
        editing: false,
        loading: false,
        currentId: null,
        formErrors: {},
        receiptFile: null,

        form: {
            expense_date: EXPENSES_DEFAULT_DATE,
            movement_type_id: '',
            amount: '',
            payment_method: '',
            description: '',
            notes: '',
            receipt_url: '',
        },

        init() {
            try {
                const raw = this.$root?.dataset?.editExpense || '';
                const edit = raw ? JSON.parse(raw) : null;
                if (edit && edit.id) {
                    this.openEditModal(edit);
                }
            } catch (e) {
                // ignore
            }
        },

        hasError(field) {
            return !!(this.formErrors && this.formErrors[field] && this.formErrors[field].length);
        },

        getError(field) {
            return this.hasError(field) ? this.formErrors[field][0] : '';
        },

        clearError(field) {
            if (this.formErrors && this.formErrors[field]) {
                delete this.formErrors[field];
            }
        },

        clearAllErrors() {
            this.formErrors = {};
        },

        openCreateModal() {
            this.editing = false;
            this.currentId = null;
            this.receiptFile = null;
            this.clearAllErrors();
            this.form = {
                expense_date: EXPENSES_DEFAULT_DATE,
                movement_type_id: '',
                amount: '',
                payment_method: '',
                description: '',
                notes: '',
                receipt_url: '',
            };
            this.modalOpen = true;
        },

        openEditModal(expense) {
            this.editing = true;
            this.currentId = expense.id;
            this.receiptFile = null;
            this.clearAllErrors();
            this.form = {
                expense_date: expense.expense_date,
                movement_type_id: String(expense.movement_type_id || ''),
                amount: expense.amount,
                payment_method: expense.payment_method || '',
                description: expense.description || '',
                notes: expense.notes || '',
                receipt_url: expense.receipt_path ? `/storage/${expense.receipt_path}` : '',
            };
            this.modalOpen = true;
        },

        closeModal() {
            if (this.loading) return;
            this.modalOpen = false;
        },

        handleFile(ev) {
            this.receiptFile = ev?.target?.files?.[0] || null;
        },

        async submitForm() {
            if (this.loading) return;
            this.loading = true;
            this.clearAllErrors();

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const url = this.editing ? `/expenses/${this.currentId}` : '/expenses';
                const method = this.editing ? 'POST' : 'POST';

                const formData = new FormData();
                Object.entries(this.form).forEach(([k, v]) => formData.append(k, v ?? ''));
                if (this.editing) formData.append('_method', 'PUT');
                if (this.receiptFile) formData.append('receipt_file', this.receiptFile);

                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    if (data.errors) {
                        this.formErrors = data.errors;
                    }
                    throw new Error(data.message || 'Error al guardar el gasto.');
                }

                window.showToast(data.message || 'Guardado.', 'success');
                this.modalOpen = false;
                setTimeout(() => window.location.reload(), 350);
            } catch (e) {
                window.showToast(e.message || 'Error al guardar el gasto.', 'error');
                this.loading = false;
                return;
            }

            this.loading = false;
        },

        async confirmDelete() {
            const ok = await SystemModal.confirm(
                'Eliminar gasto',
                '¿Desea eliminar este gasto externo?',
                'Si, eliminar',
                'Cancelar'
            );
            if (!ok) return;
            this.deleteExpense();
        },

        async confirmDeleteFromRow(id) {
            if (!id) return;
            this.editing = true;
            this.currentId = id;
            await this.confirmDelete();
        },

        async deleteExpense() {
            if (!this.editing || !this.currentId) return;
            if (this.loading) return;
            this.loading = true;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch(`/expenses/${this.currentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Error al eliminar.');
                }

                window.showToast(data.message || 'Eliminado.', 'success');
                this.modalOpen = false;
                setTimeout(() => window.location.reload(), 350);
            } catch (e) {
                window.showToast(e.message || 'Error al eliminar.', 'error');
                this.loading = false;
            }
        },
    };
}
</script>
@endsection
