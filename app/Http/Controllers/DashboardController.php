<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Consultas del día
        $consultasHoy = [
            'total' => Appointment::forDate($today)->count(),
            'completadas' => Appointment::forDate($today)->attended()->count(),
            'pendientes' => Appointment::forDate($today)->pending()->count(),
            'canceladas' => Appointment::forDate($today)->cancelled()->count(),
        ];
        
        // Ingresos del día (basado en payments de hoy)
        $paymentsHoy = Payment::whereDate('created_at', $today)->get();
        $ingresosHoy = [
            'total' => $paymentsHoy->sum('amount'),
            'efectivo' => $paymentsHoy->where('payment_method', 'cash')->sum('amount'),
            'transferencia' => $paymentsHoy->where('payment_method', 'transfer')->sum('amount'),
            'tarjeta' => $paymentsHoy->where('payment_method', 'card')->sum('amount'),
        ];
        
        // Profesionales activos
        $profesionales = Professional::active()->get();
        $profesionalesEnConsulta = $profesionales->filter(function ($prof) use ($today) {
            return $prof->appointments()
                ->forDate($today)
                ->whereTime('appointment_date', '<=', now())
                ->whereTime('appointment_date', '>', now()->subMinutes(60))
                ->pending()
                ->exists();
        });
        
        $profesionalesActivos = [
            'total' => $profesionales->count(),
            'enConsulta' => $profesionalesEnConsulta->count(),
            'disponibles' => $profesionales->count() - $profesionalesEnConsulta->count(),
        ];
        
        // Consultas detalladas del día
        $consultasDetalle = Appointment::with(['patient', 'professional'])
            ->forDate($today)
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'paciente' => $appointment->patient->full_name,
                    'profesional' => $appointment->professional->full_name,
                    'hora' => $appointment->appointment_date->format('H:i'),
                    'monto' => $appointment->final_amount ?? $appointment->estimated_amount ?? 0,
                    'status' => $appointment->status,
                    'statusLabel' => $this->getStatusLabel($appointment->status),
                ];
            });
        
        // Resumen de caja por profesional
        $profesionalesCaja = Professional::with(['appointments' => function ($query) use ($today) {
            $query->forDate($today)->attended();
        }])->active()->get()->map(function ($prof) {
            $total = $prof->appointments->sum('final_amount');
            $profesionalAmount = $prof->calculateCommission($total);
            $clinicaAmount = $prof->getClinicAmount($total);
            
            return [
                'nombre' => $prof->full_name,
                'total' => $total,
                'profesional' => $profesionalAmount,
                'clinica' => $clinicaAmount,
            ];
        })->filter(function ($prof) {
            return $prof['total'] > 0;
        });
        
        $resumenCaja = [
            'porProfesional' => $profesionalesCaja->values(),
            'totalGeneral' => $ingresosHoy['total'],
            'formasPago' => [
                'efectivo' => $ingresosHoy['efectivo'],
                'transferencia' => $ingresosHoy['transferencia'],
                'tarjeta' => $ingresosHoy['tarjeta'],
            ],
        ];
        
        $dashboardData = [
            'consultasHoy' => $consultasHoy,
            'ingresosHoy' => $ingresosHoy,
            'profesionalesActivos' => $profesionalesActivos,
            'consultasDetalle' => $consultasDetalle->values(),
            'resumenCaja' => $resumenCaja,
            'fecha' => $today->format('d/m/Y'),
        ];
        
        return view('dashboard', compact('dashboardData'));
    }
    
    private function getStatusLabel($status)
    {
        return match($status) {
            'attended' => 'Atendido',
            'scheduled' => 'Programado',
            'cancelled' => 'Cancelado',
            'absent' => 'Ausente',
            default => 'Desconocido'
        };
    }
}