@extends('layouts.app')

@section('title', 'Usuarios - ' . config('app.name'))

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
                    <span class="text-gray-500">Usuarios</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Usuarios</h1>
        @can('create', App\Models\User::class)
        <button onclick="openUserModal('create')" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuevo Usuario
        </button>
        @endcan
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/50">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rol
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Último acceso
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                                        <span class="text-white font-medium">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $user->role_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @can('update', $user)
                            <button onclick="openUserModal('edit', {{ $user->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">
                                Editar
                            </button>
                            @endcan
                            
                            @can('update', $user)
                            <button onclick="toggleUserStatus({{ $user->id }})"
                                    class="text-{{ $user->is_active ? 'orange' : 'green' }}-600 hover:text-{{ $user->is_active ? 'orange' : 'green' }}-900">
                                {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                            @endcan

                            @can('delete', $user)
                            <button onclick="deleteUser({{ $user->id }})"
                                    class="text-red-600 hover:text-red-900">
                                Eliminar
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No hay usuarios registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para crear/editar usuario -->
<div id="userModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Nuevo Usuario</h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="userForm" method="POST">
                @csrf
                <input type="hidden" id="userMethod" name="_method" value="POST">
                <input type="hidden" id="userId" name="user_id">

                <div class="space-y-4">
                    <div>
                        <label for="userName" class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input type="text" id="userName" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="userEmail" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="userEmail" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="userRole" class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                        <select id="userRole" name="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccionar rol...</option>
                            <option value="admin">Administrador</option>
                            <option value="receptionist">Recepcionista</option>
                        </select>
                    </div>

                    <div>
                        <label for="userPassword" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="passwordLabel">Contraseña</span>
                        </label>
                        <input type="password" id="userPassword" name="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1" id="passwordHelp">Mínimo 8 caracteres</p>
                    </div>

                    <div>
                        <label for="userPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña</label>
                        <input type="password" id="userPasswordConfirmation" name="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <span id="submitText">Crear Usuario</span>
                    </button>
                    <button type="button" onclick="closeUserModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentUserId = null;

function openUserModal(action, userId = null) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('modalTitle');
    const submitText = document.getElementById('submitText');
    const methodInput = document.getElementById('userMethod');
    const userIdInput = document.getElementById('userId');
    const passwordLabel = document.getElementById('passwordLabel');
    const passwordHelp = document.getElementById('passwordHelp');
    const passwordInput = document.getElementById('userPassword');

    currentUserId = userId;

    if (action === 'create') {
        title.textContent = 'Nuevo Usuario';
        submitText.textContent = 'Crear Usuario';
        form.action = "{{ route('users.store') }}";
        methodInput.value = 'POST';
        userIdInput.value = '';
        passwordLabel.textContent = 'Contraseña';
        passwordHelp.textContent = 'Mínimo 8 caracteres';
        passwordInput.required = true;
        form.reset();
    } else if (action === 'edit') {
        title.textContent = 'Editar Usuario';
        submitText.textContent = 'Actualizar Usuario';
        form.action = `/users/${userId}`;
        methodInput.value = 'PUT';
        userIdInput.value = userId;
        passwordLabel.textContent = 'Nueva Contraseña (opcional)';
        passwordHelp.textContent = 'Dejar vacío para mantener la actual';
        passwordInput.required = false;
        
        // Cargar datos del usuario
        loadUserData(userId);
    }

    modal.classList.remove('hidden');
}

function closeUserModal() {
    const modal = document.getElementById('userModal');
    modal.classList.add('hidden');
    document.getElementById('userForm').reset();
    currentUserId = null;
}

async function loadUserData(userId) {
    try {
        const response = await fetch(`/users/${userId}/edit`);
        if (response.ok) {
            const data = await response.json();
            document.getElementById('userName').value = data.name;
            document.getElementById('userEmail').value = data.email;
            document.getElementById('userRole').value = data.role;
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

async function toggleUserStatus(userId) {
    if (!confirm('¿Está seguro de cambiar el estado de este usuario?')) {
        return;
    }

    try {
        const response = await fetch(`/users/${userId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al cambiar el estado del usuario');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cambiar el estado del usuario');
    }
}

async function deleteUser(userId) {
    if (!confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch(`/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al eliminar el usuario');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el usuario');
    }
}

// Manejar envío del formulario
document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const method = formData.get('_method');
    const action = this.action;

    try {
        const response = await fetch(action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            closeUserModal();
            location.reload();
        } else if (data.errors) {
            // Mostrar errores de validación
            Object.keys(data.errors).forEach(field => {
                console.error(`${field}: ${data.errors[field].join(', ')}`);
            });
            alert('Por favor, corrija los errores en el formulario');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    }
});
</script>
@endpush
@endsection