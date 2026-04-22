<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileModule;
use App\Models\ProfilePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Mostrar listado de perfiles con sus módulos
     */
    public function index()
    {
        Gate::authorize('viewAny', Profile::class);

        $profiles = Profile::with(['modules', 'permissions'])->withCount('users')->orderBy('name')->get();
        $modules = Profile::MODULES;
        $availablePermissions = Profile::PERMISSIONS;

        return view('profiles.index', compact('profiles', 'modules', 'availablePermissions'));
    }

    /**
     * Crear nuevo perfil con módulos y permisos seleccionados
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Profile::class);

        $allPermissions = collect(Profile::PERMISSIONS)->flatMap(fn ($perms) => array_keys($perms))->toArray();

        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'string', 'max:255', 'unique:profiles'],
            'description'   => ['nullable', 'string', 'max:500'],
            'modules'       => ['nullable', 'array'],
            'modules.*'     => ['string', 'in:' . implode(',', array_keys(Profile::MODULES))],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', $allPermissions)],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile = Profile::create([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        foreach ($request->input('modules', []) as $module) {
            ProfileModule::create(['profile_id' => $profile->id, 'module' => $module]);
        }

        foreach ($request->input('permissions', []) as $permission) {
            ProfilePermission::create(['profile_id' => $profile->id, 'permission' => $permission]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil creado exitosamente',
        ]);
    }

    /**
     * Actualizar nombre, módulos y permisos de un perfil
     */
    public function update(Request $request, Profile $profile)
    {
        Gate::authorize('update', $profile);

        $allPermissions = collect(Profile::PERMISSIONS)->flatMap(fn ($perms) => array_keys($perms))->toArray();

        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'string', 'max:255', 'unique:profiles,name,' . $profile->id],
            'description'   => ['nullable', 'string', 'max:500'],
            'modules'       => ['nullable', 'array'],
            'modules.*'     => ['string', 'in:' . implode(',', array_keys(Profile::MODULES))],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', $allPermissions)],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        // Reemplazar módulos
        $profile->modules()->delete();
        foreach ($request->input('modules', []) as $module) {
            ProfileModule::create(['profile_id' => $profile->id, 'module' => $module]);
        }

        // Reemplazar permisos
        $profile->permissions()->delete();
        foreach ($request->input('permissions', []) as $permission) {
            ProfilePermission::create(['profile_id' => $profile->id, 'permission' => $permission]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
        ]);
    }

    /**
     * Eliminar perfil (verificar que no tenga usuarios)
     */
    public function destroy(Profile $profile)
    {
        Gate::authorize('delete', $profile);

        if ($profile->users()->exists()) {
            return response()->json([
                'error' => 'No se puede eliminar un perfil que tiene usuarios asignados',
            ], 400);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfil eliminado exitosamente',
        ]);
    }
}
