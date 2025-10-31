<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $selectedProfessional = $request->get('professional_id');

        // Crear fecha con día 1 para evitar overflow en meses con menos días
        // Bug: Si hoy es 31 y navegas a un mes con 30 días, Carbon hace overflow
        $date = Carbon::createFromFormat('Y-m-d', $currentMonth . '-01');
        $startOfCalendar = $date->copy()->startOfWeek();
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek();

        $professionals = Professional::active()->with('specialty')->ordered()->get();
        $patients = Patient::where('activo', true)->orderBy('last_name')->orderBy('first_name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        $appointments = [];
        $professionalSchedules = [];

        if ($selectedProfessional) {
            $appointments = Appointment::with(['patient', 'professional'])
                ->forProfessional($selectedProfessional)
                ->whereBetween('appointment_date', [$startOfCalendar, $endOfCalendar])
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('appointment_date')
                ->get()
                ->groupBy(fn ($appointment) => $appointment->appointment_date->format('Y-m-d'));

            $professionalSchedules = ProfessionalSchedule::where('professional_id', $selectedProfessional)
                ->active()
                ->get()
                ->keyBy('day_of_week');
        }

        // Estado de caja para alertas
        $today = Carbon::today();
        $cashStatus = CashMovement::getCashStatusForDate($today);

        return view('agenda.index', compact(
            'professionals',
            'patients',
            'offices',
            'selectedProfessional',
            'currentMonth',
            'date',
            'appointments',
            'professionalSchedules',
            'startOfCalendar',
            'endOfCalendar',
            'cashStatus'
        ));
    }
}
