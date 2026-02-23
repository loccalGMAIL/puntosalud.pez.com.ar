<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // Mapa de nombres de módulos en español
    private const MODULE_NAMES = [
        'Patient'                 => 'Paciente',
        'Appointment'             => 'Turno',
        'Payment'                 => 'Cobro',
        'Professional'            => 'Profesional',
        'User'                    => 'Usuario',
        'CashMovement'            => 'Movimiento de Caja',
        'ProfessionalLiquidation' => 'Liquidación',
        'Package'                 => 'Paquete',
        'PatientPackage'          => 'Paquete Paciente',
        'ProfessionalSchedule'    => 'Agenda',
        'ScheduleException'       => 'Excepción / Receso',
        'AppointmentSetting'      => 'Config. Turno',
        'Office'                  => 'Consultorio',
        'Specialty'               => 'Especialidad',
        'MovementType'            => 'Tipo Movimiento',
    ];

    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'user_id', 'action', 'subject_type']);

        $logs = ActivityLog::with('user')
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        // Estadísticas
        $today         = now()->toDateString();
        $startOfWeek   = now()->startOfWeek()->toDateString();
        $startOfMonth  = now()->startOfMonth()->toDateString();

        $stats = [
            'today'          => ActivityLog::whereDate('created_at', $today)->count(),
            'week'           => ActivityLog::whereDate('created_at', '>=', $startOfWeek)->count(),
            'month'          => ActivityLog::whereDate('created_at', '>=', $startOfMonth)->count(),
            'active_users'   => ActivityLog::whereDate('created_at', $today)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id'),
        ];

        $users       = User::orderBy('name')->get(['id', 'name']);
        $moduleNames = self::MODULE_NAMES;

        if ($request->wantsJson()) {
            return response()->json([
                'logs'        => $logs,
                'stats'       => $stats,
                'moduleNames' => $moduleNames,
            ]);
        }

        return view('activity-log.index', compact('logs', 'stats', 'users', 'filters', 'moduleNames'));
    }
}
