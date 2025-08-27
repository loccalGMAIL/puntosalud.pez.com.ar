<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Patient::orderBy('last_name')
            ->orderBy('first_name');
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('health_insurance') && $request->get('health_insurance') !== 'all') {
            $query->where('health_insurance', 'like', '%' . $request->get('health_insurance') . '%');
        }

        $patients = $query->get();

        // Estadísticas
        $allPatients = Patient::all();
        $stats = [
            'total' => $allPatients->count(),
            'with_insurance' => $allPatients->where('health_insurance', '!=', null)->where('health_insurance', '!=', '')->count(),
            'without_insurance' => $allPatients->where('health_insurance', null)->count() + $allPatients->where('health_insurance', '')->count(),
            'this_month' => $allPatients->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'patients' => $patients,
                'stats' => $stats
            ]);
        }

        return view('patients.index', compact('patients', 'stats'));
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
                'dni' => 'required|string|max:20|unique:patients',
                'birth_date' => 'required|date|before:today',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'health_insurance' => 'nullable|string|max:255',
                'health_insurance_number' => 'nullable|string|max:255',
            ]);

            Patient::create($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Paciente creado exitosamente.']);
            }

            return redirect()->route('patients.index')
                ->with('success', 'Paciente creado exitosamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        // Cargar citas del paciente
        $patient->load(['appointments.professional', 'payments']);
        
        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        // Si solo se está actualizando el estado
        if ($request->has('is_active') && !$request->has('first_name')) {
            $patient->update([
                'is_active' => $request->get('is_active') === '1'
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Estado del paciente actualizado correctamente.']);
            }

            return back()->with('success', 'Estado del paciente actualizado.');
        }

        // Actualización completa del paciente
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'dni' => 'required|string|max:20|unique:patients,dni,' . $patient->id,
                'birth_date' => 'required|date|before:today',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'health_insurance' => 'nullable|string|max:255',
                'health_insurance_number' => 'nullable|string|max:255',
            ]);

            $patient->update($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Paciente actualizado exitosamente.']);
            }

            return redirect()->route('patients.index')
                ->with('success', 'Paciente actualizado exitosamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        // Verificar si tiene citas programadas
        if ($patient->appointments()->where('status', 'scheduled')->exists()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No se puede eliminar el paciente porque tiene turnos programados.'
                ], 422);
            }
            return back()->withErrors(['error' => 'No se puede eliminar el paciente porque tiene turnos programados.']);
        }

        $patient->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Paciente eliminado exitosamente.'
            ]);
        }

        return back()->with('success', 'Paciente eliminado exitosamente.');
    }
}