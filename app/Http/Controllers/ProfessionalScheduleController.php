<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\Office;
use Illuminate\Http\Request;

class ProfessionalScheduleController extends Controller
{
    public function index(Professional $professional)
    {
        $schedules = ProfessionalSchedule::where('professional_id', $professional->id)
            ->with('office')
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');
        
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        
        $daysOfWeek = [
            1 => 'Lunes',
            2 => 'Martes', 
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        
        return view('professionals.schedules.index', compact('professional', 'schedules', 'offices', 'daysOfWeek'));
    }
    
    public function store(Request $request, Professional $professional)
    {
        // Decodificar el JSON de schedules
        $schedulesData = json_decode($request->input('schedules'), true);
        
        $validated = $request->validate([
            'schedules' => 'required|string',
        ]);
        
        // Validar la estructura de los datos decodificados
        if (!is_array($schedulesData)) {
            return response()->json([
                'success' => false,
                'message' => 'Formato de datos inválido.'
            ], 422);
        }
        
        try {
            // Eliminar todos los horarios existentes del profesional
            ProfessionalSchedule::where('professional_id', $professional->id)->delete();
            
            // Crear nuevos horarios solo para los días habilitados
            foreach ($schedulesData as $dayNumber => $scheduleData) {
                if (isset($scheduleData['enabled']) && $scheduleData['enabled']) {
                    // Validar horarios
                    if (empty($scheduleData['start_time']) || empty($scheduleData['end_time'])) {
                        continue;
                    }
                    
                    if ($scheduleData['start_time'] >= $scheduleData['end_time']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'La hora de inicio debe ser menor que la hora de fin.'
                        ], 422);
                    }
                    
                    ProfessionalSchedule::create([
                        'professional_id' => $professional->id,
                        'day_of_week' => $scheduleData['day_of_week'],
                        'start_time' => $scheduleData['start_time'],
                        'end_time' => $scheduleData['end_time'],
                        'office_id' => !empty($scheduleData['office_id']) ? $scheduleData['office_id'] : null,
                        'is_active' => true,
                    ]);
                }
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Horarios actualizados exitosamente.'
                ]);
            }
            
            return redirect()->route('professionals.schedules.index', $professional)
                ->with('success', 'Horarios actualizados exitosamente.');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar los horarios: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar los horarios: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    public function update(Request $request, Professional $professional, ProfessionalSchedule $schedule)
    {
        // Validar que el horario pertenezca al profesional
        if ($schedule->professional_id !== $professional->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'office_id' => 'nullable|exists:offices,id',
            'is_active' => 'boolean',
        ]);
        
        $schedule->update($validated);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Horario actualizado exitosamente.'
            ]);
        }
        
        return redirect()->route('professionals.schedules.index', $professional)
            ->with('success', 'Horario actualizado exitosamente.');
    }
    
    public function destroy(Professional $professional, ProfessionalSchedule $schedule)
    {
        // Validar que el horario pertenezca al profesional
        if ($schedule->professional_id !== $professional->id) {
            abort(404);
        }
        
        $schedule->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Horario eliminado exitosamente.'
            ]);
        }
        
        return redirect()->route('professionals.schedules.index', $professional)
            ->with('success', 'Horario eliminado exitosamente.');
    }
}