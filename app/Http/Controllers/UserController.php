<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Mostrar listado de usuarios
     */
    public function index()
    {
        $users = User::with('lastLogin')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Mostrar formulario para crear usuario
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Almacenar nuevo usuario
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,receptionist'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario creado exitosamente']);
    }

    /**
     * Mostrar información de un usuario
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Mostrar formulario para editar usuario
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,receptionist'],
        ];

        // Solo validar password si se proporciona
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Solo actualizar password si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        // No permitir eliminar al usuario logueado
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'No puede eliminar su propio usuario'], 400);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    }

    /**
     * Cambiar estado de usuario (activo/inactivo)
     */
    public function toggleStatus(User $user)
    {
        // No permitir desactivar al usuario logueado
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'No puede desactivar su propio usuario'], 400);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activado' : 'desactivado';

        return response()->json([
            'success' => true,
            'message' => "Usuario {$status} exitosamente",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Cambiar password propio
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        // Verificar password actual
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['La contraseña actual es incorrecta']]], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada exitosamente']);
    }

    /**
     * Mostrar perfil del usuario logueado
     */
    public function profile()
    {
        $user = auth()->user();

        return view('users.profile', compact('user'));
    }
}
