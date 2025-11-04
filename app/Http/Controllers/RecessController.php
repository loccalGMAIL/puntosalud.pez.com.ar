<?php

namespace App\Http\Controllers;

use App\Models\ScheduleException;
use Illuminate\Http\Request;

class RecessController extends Controller
{
    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $query = ScheduleException::holidays()
            ->active()
            ->orderBy('exception_date', 'desc');

        // Filtros
        if ($request->filled('year')) {
            $year = $request->get('year');
            $query->whereYear('exception_date', $year);
        }

        $holidays = $query->paginate(20)->withQueryString();

        // Obtener años disponibles para el filtro
        $years = ScheduleException::holidays()
            ->selectRaw('DISTINCT YEAR(exception_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'holidays' => $holidays->items(),
                'pagination' => [
                    'current_page' => $holidays->currentPage(),
                    'last_page' => $holidays->lastPage(),
                    'per_page' => $holidays->perPage(),
                    'total' => $holidays->total(),
                ],
            ]);
        }

        return view('recesos.index', compact('holidays', 'years'));
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exception_date' => 'required|date|unique:schedule_exceptions,exception_date,NULL,id,type,holiday',
                'reason' => 'required|string|max:255',
            ], [
                'exception_date.required' => 'La fecha es obligatoria.',
                'exception_date.date' => 'La fecha no es válida.',
                'exception_date.unique' => 'Ya existe un feriado en esta fecha.',
                'reason.required' => 'La descripción es obligatoria.',
            ]);

            ScheduleException::create([
                'exception_date' => $validated['exception_date'],
                'reason' => $validated['reason'],
                'type' => 'holiday',
                'affects_all' => true,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Feriado creado exitosamente.']);
            }

            return redirect()->route('recesos.index')
                ->with('success', 'Feriado creado exitosamente.');

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
     * Update the specified holiday.
     */
    public function update(Request $request, ScheduleException $receso)
    {
        // Verificar que sea un feriado
        if ($receso->type !== 'holiday') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar feriados.',
            ], 422);
        }

        try {
            $validated = $request->validate([
                'exception_date' => 'required|date|unique:schedule_exceptions,exception_date,' . $receso->id . ',id,type,holiday',
                'reason' => 'required|string|max:255',
            ], [
                'exception_date.required' => 'La fecha es obligatoria.',
                'exception_date.date' => 'La fecha no es válida.',
                'exception_date.unique' => 'Ya existe un feriado en esta fecha.',
                'reason.required' => 'La descripción es obligatoria.',
            ]);

            $receso->update($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Feriado actualizado exitosamente.']);
            }

            return redirect()->route('recesos.index')
                ->with('success', 'Feriado actualizado exitosamente.');

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
     * Remove the specified holiday (permanent delete).
     */
    public function destroy(Request $request, ScheduleException $receso)
    {
        // Verificar que sea un feriado
        if ($receso->type !== 'holiday') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar feriados.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Solo se pueden eliminar feriados.']);
        }

        // Eliminar permanentemente
        $receso->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Feriado eliminado exitosamente.',
            ]);
        }

        return back()->with('success', 'Feriado eliminado exitosamente.');
    }

    /**
     * Toggle active status of holiday.
     */
    public function toggleStatus(Request $request, ScheduleException $receso)
    {
        // Verificar que sea un feriado
        if ($receso->type !== 'holiday') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden activar/desactivar feriados.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Solo se pueden activar/desactivar feriados.']);
        }

        // Cambiar estado
        $receso->update(['is_active' => !$receso->is_active]);

        $message = $receso->is_active ? 'Feriado activado exitosamente.' : 'Feriado desactivado exitosamente.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
