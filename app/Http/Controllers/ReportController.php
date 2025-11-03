<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Listado diario de pacientes a atender por profesional
     */
    public function dailySchedule(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');
        $selectedDate = Carbon::parse($date);

        // Obtener profesionales activos que tienen turnos en la fecha seleccionada
        $professionalsWithAppointments = Professional::active()
            ->with(['specialty', 'appointments' => function ($query) use ($selectedDate) {
                $query->forDate($selectedDate);
            }])
            ->whereHas('appointments', function ($query) use ($selectedDate) {
                $query->forDate($selectedDate);
            })
            ->orderBy('last_name')
            ->get()
            ->map(function ($professional) {
                return [
                    'id' => $professional->id,
                    'first_name' => $professional->first_name,
                    'last_name' => $professional->last_name,
                    'full_name' => $professional->full_name,
                    'specialty' => $professional->specialty,
                    'appointments_count' => $professional->appointments->count(),
                    'first_appointment_time' => $professional->appointments->min('appointment_date'),
                    'last_appointment_time' => $professional->appointments->max('appointment_date'),
                ];
            });

        // Obtener todos los profesionales activos para el dropdown
        $allProfessionals = Professional::active()
            ->with('specialty')
            ->orderBy('last_name')
            ->get();

        // Si no se especifica profesional, mostrar vista de selección
        if (! $professionalId) {
            return view('reports.daily-schedule-select', compact('allProfessionals', 'professionalsWithAppointments', 'selectedDate'));
        }

        // Obtener el profesional seleccionado
        $professional = Professional::with('specialty')->findOrFail($professionalId);

        // Obtener pacientes del día para el profesional
        $appointments = Appointment::with(['patient', 'paymentAppointments.payment'])
            ->where('professional_id', $professionalId)
            ->forDate($selectedDate)
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'time' => $appointment->appointment_date->format('H:i'),
                    'patient_name' => $appointment->patient->full_name,
                    'patient_phone' => $appointment->patient->phone,
                    'patient_email' => $appointment->patient->email,
                    'patient_dni' => $appointment->patient->dni,
                    'patient_insurance' => $appointment->patient->insurance_company,
                    'estimated_amount' => $appointment->estimated_amount ?? 0,
                    'final_amount' => $appointment->final_amount,
                    'status' => $appointment->status,
                    'status_label' => $this->getStatusLabel($appointment->status),
                    'is_paid' => $appointment->paymentAppointments()->exists(),
                    'payment_method' => $appointment->paymentAppointments->first()?->payment?->payment_method,
                    'notes' => $appointment->notes,
                    'office' => $appointment->office?->name ?? 'No asignado',
                    'is_urgency' => $appointment->is_urgency,
                    'is_between_turn' => $appointment->is_between_turn,
                ];
            })
            ->sortByDesc('is_urgency') // Urgencias primero
            ->values();

        // Estadísticas del día
        $stats = [
            'total_appointments' => $appointments->count(),
            'scheduled' => $appointments->where('status', 'scheduled')->count(),
            'attended' => $appointments->where('status', 'attended')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'absent' => $appointments->where('status', 'absent')->count(),
            'paid_appointments' => $appointments->where('is_paid', true)->count(),
            'total_estimated' => $appointments->sum('estimated_amount'),
            'total_final' => $appointments->whereNotNull('final_amount')->sum('final_amount'),
        ];

        // Información adicional
        $reportData = [
            'professional' => $professional,
            'date' => $selectedDate,
            'appointments' => $appointments,
            'stats' => $stats,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'Sistema',
        ];

        // Si es para imprimir, devolver vista de impresión
        if ($request->get('print') === '1') {
            return view('reports.daily-schedule-print', compact('reportData'));
        }

        // Vista normal
        return view('reports.daily-schedule', compact('reportData', 'allProfessionals'));
    }

    /**
     * Resumen diario de pacientes por profesional
     */
    public function dailySummary(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $professionals = Professional::active()
            ->with(['specialty', 'appointments' => function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)
                    ->with(['patient', 'paymentAppointments.payment']);
            }])
            ->orderBy('last_name')
            ->get()
            ->map(function ($professional) {
                $appointments = $professional->appointments;

                return [
                    'id' => $professional->id,
                    'name' => $professional->full_name,
                    'specialty' => $professional->specialty->name,
                    'total_appointments' => $appointments->count(),
                    'scheduled' => $appointments->where('status', 'scheduled')->count(),
                    'attended' => $appointments->where('status', 'attended')->count(),
                    'cancelled' => $appointments->where('status', 'cancelled')->count(),
                    'absent' => $appointments->where('status', 'absent')->count(),
                    'paid' => $appointments->filter(fn ($apt) => $apt->paymentAppointments->isNotEmpty())->count(),
                    'total_estimated' => $appointments->sum('estimated_amount'),
                    'total_final' => $appointments->whereNotNull('final_amount')->sum('final_amount'),
                    'first_appointment' => $appointments->min('appointment_date'),
                    'last_appointment' => $appointments->max('appointment_date'),
                ];
            })
            ->filter(function ($professional) {
                return $professional['total_appointments'] > 0;
            });

        $summaryData = [
            'date' => $selectedDate,
            'professionals' => $professionals,
            'totals' => [
                'total_appointments' => $professionals->sum('total_appointments'),
                'total_scheduled' => $professionals->sum('scheduled'),
                'total_attended' => $professionals->sum('attended'),
                'total_cancelled' => $professionals->sum('cancelled'),
                'total_absent' => $professionals->sum('absent'),
                'total_paid' => $professionals->sum('paid'),
                'total_estimated' => $professionals->sum('total_estimated'),
                'total_final' => $professionals->sum('total_final'),
            ],
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'Sistema',
        ];

        if ($request->get('print') === '1') {
            return view('reports.daily-summary-print', compact('summaryData'));
        }

        return view('reports.daily-summary', compact('summaryData'));
    }

    /**
     * Reporte de liquidación diaria para profesional
     */
    public function professionalLiquidation(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');
        $selectedDate = Carbon::parse($date);

        // Obtener profesionales con turnos atendidos en la fecha
        $professionalsWithLiquidation = Professional::active()
            ->with(['specialty', 'appointments' => function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)->attended();
            }])
            ->whereHas('appointments', function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)->attended();
            })
            ->orderBy('last_name')
            ->get()
            ->map(function ($professional) use ($selectedDate) {
                $totalAmount = $professional->appointments->sum('final_amount');
                $professionalCommission = $professional->calculateCommission($totalAmount);

                // Obtener reintegros del día para este profesional (usando referencias polimórficas)
                $refunds = \App\Models\CashMovement::byType('expense')
                    ->where('reference_type', 'App\Models\Professional')
                    ->where('reference_id', $professional->id)
                    ->whereDate('created_at', $selectedDate)
                    ->get();

                $totalRefunds = $refunds->sum(function($refund) {
                    return abs($refund->amount); // Los gastos son negativos, convertir a positivo
                });

                return [
                    'id' => $professional->id,
                    'full_name' => $professional->full_name,
                    'specialty' => $professional->specialty,
                    'attended_count' => $professional->appointments->count(),
                    'total_amount' => $totalAmount,
                    'refunds' => $totalRefunds,
                    'professional_amount' => $professionalCommission - $totalRefunds,
                    'clinic_amount' => $totalAmount - $professionalCommission,
                ];
            })
            ->filter(function ($professional) {
                return $professional['attended_count'] > 0;
            });

        // Obtener todos los profesionales para el dropdown
        $allProfessionals = Professional::active()
            ->with('specialty')
            ->orderBy('last_name')
            ->get();

        // Si no se especifica profesional, mostrar vista de selección
        if (! $professionalId) {
            return view('reports.professional-liquidation-select', compact('allProfessionals', 'professionalsWithLiquidation', 'selectedDate'));
        }

        // Obtener el profesional seleccionado
        $professional = Professional::with('specialty')->findOrFail($professionalId);

        // Obtener turnos atendidos del día con información de pagos
        $attendedAppointments = Appointment::with(['patient', 'paymentAppointments.payment'])
            ->where('professional_id', $professionalId)
            ->forDate($selectedDate)
            ->attended()
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) use ($selectedDate) {
                $paymentAppointment = $appointment->paymentAppointments->first();
                $payment = $paymentAppointment ? $paymentAppointment->payment : null;

                // Determinar si fue pago anticipado o del día
                $isPrepaid = false;
                $paymentDate = null;
                if ($payment) {
                    $paymentDate = Carbon::parse($payment->payment_date);
                    $isPrepaid = ! $paymentDate->isSameDay($selectedDate);
                }

                return [
                    'id' => $appointment->id,
                    'time' => $appointment->appointment_date->format('H:i'),
                    'patient_name' => $appointment->patient->full_name,
                    'patient_dni' => $appointment->patient->dni,
                    'final_amount' => $appointment->final_amount ?? 0,
                    'is_paid' => $paymentAppointment ? true : false,
                    'payment_method' => $payment ? $payment->payment_method : null,
                    'payment_date' => $paymentDate ? $paymentDate->format('d/m/Y') : null,
                    'is_prepaid' => $isPrepaid,
                    'payment_type' => $payment ? $payment->payment_type : null,
                    'receipt_number' => $payment ? $payment->receipt_number : null,
                    'is_urgency' => $appointment->is_urgency,
                    'duration' => $appointment->duration,
                ];
            });

        // Calcular estadísticas de liquidación
        $totalAmount = $attendedAppointments->sum('final_amount');
        $professionalCommission = $professional->calculateCommission($totalAmount);

        // Obtener reintegros del día para este profesional (usando referencias polimórficas)
        $refunds = \App\Models\CashMovement::byType('expense')
            ->where('reference_type', 'App\Models\Professional')
            ->where('reference_id', $professionalId)
            ->whereDate('created_at', $selectedDate)
            ->get();

        $totalRefunds = $refunds->sum(function($refund) {
            return abs($refund->amount); // Los gastos son negativos, convertir a positivo
        });

        $clinicAmount = $totalAmount - $professionalCommission;
        $finalProfessionalAmount = $professionalCommission - $totalRefunds;

        // Separar por tipo de pago
        $prepaidAppointments = $attendedAppointments->where('is_prepaid', true);
        $todayPaidAppointments = $attendedAppointments->where('is_paid', true)->where('is_prepaid', false);
        $unpaidAppointments = $attendedAppointments->where('is_paid', false);

        $liquidationData = [
            'professional' => $professional,
            'date' => $selectedDate,
            'appointments' => $attendedAppointments,
            'prepaid_appointments' => $prepaidAppointments,
            'today_paid_appointments' => $todayPaidAppointments,
            'unpaid_appointments' => $unpaidAppointments,
            'refunds' => $refunds,
            'totals' => [
                'total_amount' => $totalAmount,
                'professional_commission' => $professionalCommission,
                'total_refunds' => $totalRefunds,
                'professional_amount' => $finalProfessionalAmount,
                'clinic_amount' => $clinicAmount,
                'commission_percentage' => $professional->commission_percentage,
                'prepaid_amount' => $prepaidAppointments->sum('final_amount'),
                'today_paid_amount' => $todayPaidAppointments->sum('final_amount'),
                'unpaid_amount' => $unpaidAppointments->sum('final_amount'),
                'prepaid_professional' => $professional->calculateCommission($prepaidAppointments->sum('final_amount')),
                'today_paid_professional' => $professional->calculateCommission($todayPaidAppointments->sum('final_amount')),
                'unpaid_professional' => $professional->calculateCommission($unpaidAppointments->sum('final_amount')),
            ],
            'payment_methods_summary' => $attendedAppointments->where('is_paid', true)->groupBy('payment_method')->map(function ($group, $method) {
                return [
                    'method' => $method,
                    'count' => $group->count(),
                    'amount' => $group->sum('final_amount'),
                ];
            }),
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'Sistema',
        ];

        // Si es para imprimir, devolver vista de impresión
        if ($request->get('print') === '1') {
            return view('reports.professional-liquidation-print', compact('liquidationData'));
        }

        // Vista normal
        return view('reports.professional-liquidation', compact('liquidationData', 'allProfessionals'));
    }

    /**
     * Etiquetas de estado de citas
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'attended' => 'Atendido',
            'scheduled' => 'Programado',
            'cancelled' => 'Cancelado',
            'absent' => 'Ausente',
            default => 'Desconocido'
        };
    }
}
