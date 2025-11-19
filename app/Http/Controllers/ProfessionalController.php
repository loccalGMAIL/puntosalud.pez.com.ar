<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Professional::with('specialty')
            ->orderBy('last_name')
            ->orderBy('first_name');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            // Limpiar búsqueda de puntos para DNI
            $cleanSearch = preg_replace('/[.\s]/', '', $search);

            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    // Búsqueda de DNI sin puntos (normalizada)
                    ->orWhereRaw('REPLACE(dni, ".", "") LIKE ?', ["%{$cleanSearch}%"]);
            });
        }

        if ($request->filled('specialty') && $request->get('specialty') !== 'all') {
            $query->where('specialty_id', $request->get('specialty'));
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $isActive = $request->get('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $professionals = $query->paginate(15)->withQueryString();
        $specialties = Specialty::orderBy('name')->get();

        // Estadísticas
        $allProfessionals = Professional::all();
        $stats = [
            'total' => $allProfessionals->count(),
            'active' => $allProfessionals->where('is_active', true)->count(),
            'inactive' => $allProfessionals->where('is_active', false)->count(),
            'specialties_count' => $specialties->count(),
        ];

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'professionals' => $professionals->items(),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $professionals->currentPage(),
                    'last_page' => $professionals->lastPage(),
                    'per_page' => $professionals->perPage(),
                    'total' => $professionals->total(),
                ],
            ]);
        }

        return view('professionals.index', compact('professionals', 'specialties', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialties = Specialty::orderBy('name')->get();

        return view('professionals.create', compact('specialties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
                'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
                'email' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9._%+\-ñÑ]+@[a-zA-Z0-9.\-ñÑ]+\.[a-zA-Z]{2,}$/'],
                'phone' => 'nullable|string|max:255',
                'dni' => ['required', 'string', 'max:20', 'unique:professionals', 'regex:/^[0-9.]+$/'],
                'license_number' => 'nullable|string|max:255',
                'specialty_id' => 'required|exists:specialties,id',
                'commission_percentage' => 'required|numeric|min:0|max:100',
                'receives_transfers_directly' => 'boolean',
                'notes' => 'nullable|string|max:1000',
            ], [
                'first_name.regex' => 'El nombre solo puede contener letras y espacios.',
                'last_name.regex' => 'El apellido solo puede contener letras y espacios.',
                'dni.regex' => 'El DNI solo puede contener números y puntos.',
                'dni.unique' => 'El DNI ingresado ya está registrado en el sistema.',
                'email.regex' => 'El email solo puede contener letras sin acentos, números, puntos, guiones y la letra ñ.',
            ]);

            // Formatear DNI con puntos
            $validated['dni'] = $this->formatDni($validated['dni']);
            $validated['is_active'] = true; // Por defecto activo

            // Convertir receives_transfers_directly a booleano (por defecto false si no se envía)
            $validated['receives_transfers_directly'] = $request->boolean('receives_transfers_directly');

            Professional::create($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Profesional creado exitosamente.']);
            }

            return redirect()->route('professionals.index')
                ->with('success', 'Profesional creado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar error de constraint violation (DNI duplicado)
            if ($e->errorInfo[1] == 1062) { // Código MySQL para duplicate entry
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El DNI ingresado ya está registrado en el sistema. Por favor, verifique el número de documento.',
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->withErrors(['dni' => 'El DNI ingresado ya está registrado en el sistema.']);
            }

            // Si es otro tipo de error de base de datos, re-lanzarlo
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Professional $professional)
    {
        $professional->load('specialty');

        return view('professionals.show', compact('professional'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Professional $professional)
    {
        $specialties = Specialty::orderBy('name')->get();

        return view('professionals.edit', compact('professional', 'specialties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professional $professional)
    {
        // Si solo se está actualizando el estado
        if ($request->has('is_active') && ! $request->has('first_name')) {
            $professional->update([
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
            }

            return back()->with('success', 'Estado del profesional actualizado.');
        }

        // Actualización completa del profesional
        try {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
                'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
                'email' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9._%+\-ñÑ]+@[a-zA-Z0-9.\-ñÑ]+\.[a-zA-Z]{2,}$/'],
                'phone' => 'nullable|string|max:255',
                'dni' => ['required', 'string', 'max:20', 'unique:professionals,dni,'.$professional->id, 'regex:/^[0-9.]+$/'],
                'license_number' => 'nullable|string|max:255',
                'specialty_id' => 'required|exists:specialties,id',
                'commission_percentage' => 'required|numeric|min:0|max:100',
                'receives_transfers_directly' => 'boolean',
                'notes' => 'nullable|string|max:1000',
                'is_active' => 'required|in:0,1',
            ], [
                'first_name.regex' => 'El nombre solo puede contener letras y espacios.',
                'last_name.regex' => 'El apellido solo puede contener letras y espacios.',
                'dni.regex' => 'El DNI solo puede contener números y puntos.',
                'dni.unique' => 'El DNI ingresado ya está registrado en el sistema.',
                'email.regex' => 'El email solo puede contener letras sin acentos, números, puntos, guiones y la letra ñ.',
            ]);

            // Formatear DNI con puntos
            $validated['dni'] = $this->formatDni($validated['dni']);

            // Convertir is_active a booleano
            $validated['is_active'] = $validated['is_active'] === '1';

            // Convertir receives_transfers_directly a booleano
            $validated['receives_transfers_directly'] = $request->boolean('receives_transfers_directly');

            $professional->update($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Profesional actualizado exitosamente.']);
            }

            return redirect()->route('professionals.index')
                ->with('success', 'Profesional actualizado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar error de constraint violation (DNI duplicado)
            if ($e->errorInfo[1] == 1062) { // Código MySQL para duplicate entry
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El DNI ingresado ya está registrado en el sistema. Por favor, verifique el número de documento.',
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->withErrors(['dni' => 'El DNI ingresado ya está registrado en el sistema.']);
            }

            // Si es otro tipo de error de base de datos, re-lanzarlo
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professional $professional)
    {
        // En lugar de eliminar, marcamos como inactivo
        $professional->update(['is_active' => false]);

        return redirect()->route('professionals.index')
            ->with('success', 'Profesional desactivado exitosamente.');
    }

    /**
     * Toggle professional status
     */
    public function toggleStatus(Professional $professional, Request $request)
    {
        $professional->update([
            'is_active' => ! $professional->is_active,
        ]);

        $message = $professional->is_active ? 'Profesional activado correctamente.' : 'Profesional desactivado correctamente.';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Formatear DNI agregando puntos si no los tiene
     */
    private function formatDni($dni)
    {
        if (empty($dni)) {
            return $dni;
        }

        // Remover todos los puntos y espacios existentes
        $cleanDni = preg_replace('/[.\s]/', '', $dni);

        // Verificar que solo contenga números
        if (! preg_match('/^\d{7,8}$/', $cleanDni)) {
            return $dni; // Devolver original si no es válido
        }

        // Formatear según la longitud
        if (strlen($cleanDni) === 7) {
            // 7 dígitos: X.XXX.XXX
            return substr($cleanDni, 0, 1).'.'.substr($cleanDni, 1, 3).'.'.substr($cleanDni, 4, 3);
        } elseif (strlen($cleanDni) === 8) {
            // 8 dígitos: XX.XXX.XXX
            return substr($cleanDni, 0, 2).'.'.substr($cleanDni, 2, 3).'.'.substr($cleanDni, 5, 3);
        }

        return $dni; // Devolver original si no coincide con formatos esperados
    }
}
