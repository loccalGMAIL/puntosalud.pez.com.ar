<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ScheduleException;
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

        // Obtener profesionales más frecuentes (por cantidad de turnos) para mostrar como favoritos
        $topProfessionals = Professional::active()
            ->with('specialty')
            ->withCount(['appointments' => function ($query) {
                $query->whereIn('status', ['scheduled', 'attended']);
            }])
            ->having('appointments_count', '>', 0)
            ->orderBy('appointments_count', 'desc')
            ->take(6)
            ->get();

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

        // Obtener feriados activos del rango de fechas del calendario
        $holidays = ScheduleException::holidays()
            ->active()
            ->whereBetween('exception_date', [$startOfCalendar, $endOfCalendar])
            ->get()
            ->keyBy(fn($holiday) => $holiday->exception_date->format('Y-m-d'));

        // Obtener cumpleaños de profesionales activos
        $birthdays = Professional::active()
            ->whereNotNull('birthday')
            ->with('specialty')
            ->get()
            ->groupBy(function($prof) use ($startOfCalendar) {
                if (!$prof->birthday) return null;

                // Obtener mes-día del cumpleaños
                $birthMonth = $prof->birthday->format('m');
                $birthDay = $prof->birthday->format('d');

                // Calcular año del cumpleaños en el rango del calendario
                $year = $startOfCalendar->year;
                $birthdayThisYear = Carbon::createFromFormat('Y-m-d', "{$year}-{$birthMonth}-{$birthDay}");

                // Si el cumpleaños ya pasó en este año del calendario, usar el año siguiente
                if ($birthdayThisYear->lt($startOfCalendar)) {
                    $year++;
                    $birthdayThisYear = Carbon::createFromFormat('Y-m-d', "{$year}-{$birthMonth}-{$birthDay}");
                }

                return $birthdayThisYear->format('Y-m-d');
            })
            ->filter()
            ->map(function($professionals, $date) {
                return $professionals->map(function($prof) use ($date) {
                    // Calcular la edad que cumple en esta fecha
                    $birthdayYear = Carbon::parse($date)->year;
                    $birthYear = $prof->birthday->year;
                    $age = $birthdayYear - $birthYear;

                    return [
                        'id' => $prof->id,
                        'name' => $prof->full_name,
                        'specialty' => $prof->specialty->name,
                        'age' => $age
                    ];
                });
            });

        // Estado de caja para alertas
        $today = Carbon::today();
        $cashStatus = CashMovement::getCashStatusForDate($today);

        return view('agenda.index', compact(
            'professionals',
            'patients',
            'offices',
            'topProfessionals',
            'selectedProfessional',
            'currentMonth',
            'date',
            'appointments',
            'professionalSchedules',
            'startOfCalendar',
            'endOfCalendar',
            'holidays',
            'birthdays',
            'cashStatus'
        ));
    }
}
