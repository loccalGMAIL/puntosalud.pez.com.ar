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
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
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
                'professionals' => $professionals,
                'stats' => $stats,
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
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'nullable|string|max:255',
                'dni' => 'required|string|max:20|unique:professionals',
                'specialty_id' => 'required|exists:specialties,id',
                'commission_percentage' => 'required|numeric|min:0|max:100',
            ]);

            $validated['is_active'] = true; // Por defecto activo

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
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'nullable|string|max:255',
                'dni' => 'required|string|max:20|unique:professionals,dni,'.$professional->id,
                'specialty_id' => 'required|exists:specialties,id',
                'commission_percentage' => 'required|numeric|min:0|max:100',
                'is_active' => 'required|in:0,1',
            ]);

            // Convertir is_active a booleano
            $validated['is_active'] = $validated['is_active'] === '1';

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
}
