@extends('layouts.app')

@section('title', 'Editar Tipo de Movimiento - ' . config('app.name'))
@section('mobileTitle', 'Editar Tipo')

@section('content')
<div class="flex h-full flex-1 flex-col gap-6 p-4">

    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Configuración</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('movement-types.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Tipos de Movimientos</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Editar</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Editar Tipo de Movimiento
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Modifica los datos del tipo: <strong>{{ $movementType->icon }} {{ $movementType->name }}</strong>
            </p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="{{ route('movement-types.update', $movementType) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Información Básica -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información Básica</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Código -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Código <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="code"
                               id="code"
                               value="{{ old('code', $movementType->code) }}"
                               required
                               placeholder="ej: patient_payment"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('code') border-red-500 @enderror">
                        @error('code')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Identificador único en snake_case</p>
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $movementType->name) }}"
                               required
                               placeholder="ej: Pago de Paciente"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descripción
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  placeholder="Descripción detallada del tipo de movimiento"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror">{{ old('description', $movementType->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Clasificación -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Clasificación</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Categoría -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categoría <span class="text-red-500">*</span>
                        </label>
                        <select name="category"
                                id="category"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('category') border-red-500 @enderror">
                            <option value="">Seleccionar categoría...</option>
                            <option value="main_type" {{ old('category', $movementType->category) == 'main_type' ? 'selected' : '' }}>Tipo Principal</option>
                            <option value="expense_detail" {{ old('category', $movementType->category) == 'expense_detail' ? 'selected' : '' }}>Detalle de Egreso</option>
                            <option value="income_detail" {{ old('category', $movementType->category) == 'income_detail' ? 'selected' : '' }}>Detalle de Ingreso</option>
                            <option value="withdrawal_detail" {{ old('category', $movementType->category) == 'withdrawal_detail' ? 'selected' : '' }}>Detalle de Retiro</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo Padre -->
                    <div>
                        <label for="parent_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo Padre
                        </label>
                        <select name="parent_type_id"
                                id="parent_type_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('parent_type_id') border-red-500 @enderror">
                            <option value="">Sin padre (tipo principal)</option>
                            @foreach($parentTypes as $parentType)
                                <option value="{{ $parentType->id }}" {{ old('parent_type_id', $movementType->parent_type_id) == $parentType->id ? 'selected' : '' }}>
                                    {{ $parentType->icon }} {{ $parentType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_type_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dejar vacío si es un tipo principal</p>
                    </div>

                    <!-- Afecta Balance -->
                    <div>
                        <label for="affects_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Afecta Balance <span class="text-red-500">*</span>
                        </label>
                        <select name="affects_balance"
                                id="affects_balance"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('affects_balance') border-red-500 @enderror">
                            <option value="1" {{ old('affects_balance', $movementType->affects_balance) == '1' ? 'selected' : '' }}>+1 (Ingreso)</option>
                            <option value="0" {{ old('affects_balance', $movementType->affects_balance) == '0' ? 'selected' : '' }}>0 (Neutral)</option>
                            <option value="-1" {{ old('affects_balance', $movementType->affects_balance) == '-1' ? 'selected' : '' }}>-1 (Egreso)</option>
                        </select>
                        @error('affects_balance')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Orden -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Orden
                        </label>
                        <input type="number"
                               name="order"
                               id="order"
                               value="{{ old('order', $movementType->order) }}"
                               min="0"
                               placeholder="0"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('order') border-red-500 @enderror">
                        @error('order')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Orden de visualización (menor = primero)</p>
                    </div>

                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Apariencia -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Apariencia</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Icono -->
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Icono (Emoji)
                        </label>
                        <input type="text"
                               name="icon"
                               id="icon"
                               value="{{ old('icon', $movementType->icon) }}"
                               maxlength="10"
                               placeholder="💰"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('icon') border-red-500 @enderror">
                        @error('icon')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Emoji que representa el tipo</p>
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Color
                        </label>
                        <select name="color"
                                id="color"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white @error('color') border-red-500 @enderror">
                            <option value="">Sin color específico</option>
                            <option value="green" {{ old('color', $movementType->color) == 'green' ? 'selected' : '' }}>Verde</option>
                            <option value="blue" {{ old('color', $movementType->color) == 'blue' ? 'selected' : '' }}>Azul</option>
                            <option value="red" {{ old('color', $movementType->color) == 'red' ? 'selected' : '' }}>Rojo</option>
                            <option value="yellow" {{ old('color', $movementType->color) == 'yellow' ? 'selected' : '' }}>Amarillo</option>
                            <option value="purple" {{ old('color', $movementType->color) == 'purple' ? 'selected' : '' }}>Púrpura</option>
                            <option value="orange" {{ old('color', $movementType->color) == 'orange' ? 'selected' : '' }}>Naranja</option>
                            <option value="gray" {{ old('color', $movementType->color) == 'gray' ? 'selected' : '' }}>Gris</option>
                        </select>
                        @error('color')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Color para badges y etiquetas</p>
                    </div>

                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Estado -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estado</h2>
                <div class="flex items-center">
                    <input type="checkbox"
                           name="is_active"
                           id="is_active"
                           value="1"
                           {{ old('is_active', $movementType->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Activo
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Los tipos inactivos no aparecerán en los formularios</p>
            </div>

            <!-- Información adicional -->
            @if($movementType->cashMovements()->exists())
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                Este tipo tiene <strong>{{ $movementType->cashMovements()->count() }} movimientos</strong> asociados. Cambiar ciertos campos puede afectar los reportes históricos.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('movement-types.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors duration-200">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Guardar Cambios
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
