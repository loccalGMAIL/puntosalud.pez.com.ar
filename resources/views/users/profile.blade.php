@extends('layouts.app')

@section('title', 'Mi Perfil - ' . config('app.name'))

@section('content')
<div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
    
    <!-- Breadcrumb -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600">Dashboard</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-500">Mi Perfil</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mi Perfil</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Información del Usuario -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Personal</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <div class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-900">
                            {{ $user->name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <div class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-900">
                            {{ $user->email }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Perfil</label>
                        @if($user->profile)
                            <div class="inline-flex px-3 py-2 text-sm font-semibold rounded-full
                                {{ $user->profile->modules->contains('module', 'configuration') ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $user->profile->name }}
                            </div>
                        @else
                            <div class="inline-flex px-3 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Sin perfil asignado
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <div class="inline-flex px-3 py-2 text-sm font-semibold rounded-full 
                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Miembro desde</label>
                        <div class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-900">
                            {{ $user->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Cambiar Contraseña</h2>
                
                <form id="changePasswordForm" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña Actual
                        </label>
                        <input type="password" 
                               id="currentPassword" 
                               name="current_password" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Contraseña
                        </label>
                        <input type="password" 
                               id="newPassword" 
                               name="password" 
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                    </div>

                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" 
                               id="confirmPassword" 
                               name="password_confirmation" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
@push('scripts')
<script>
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('{{ route("change-password") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            window.showToast('Contraseña actualizada exitosamente', 'success');
            this.reset();
        } else if (data.errors) {
            // Mostrar errores de validación
            let errorMessages = [];
            Object.keys(data.errors).forEach(field => {
                errorMessages.push(...data.errors[field]);
            });
            window.showToast('Errores: ' + errorMessages.join(', '), 'error');
        } else {
            window.showToast('Error al cambiar la contraseña', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        window.showToast('Error al procesar la solicitud', 'error');
    }
});
</script>
@endpush
@endsection