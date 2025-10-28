<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\MovementType;
use App\Models\Payment;
use App\Models\Professional;
use App\Services\PaymentAllocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $paymentAllocationService;

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        $this->paymentAllocationService = $paymentAllocationService;
    }

    public function index()
    {
        $today = Carbon::today();

        // Verificar estado de caja para recepcionistas
        // $cashStatus = null;
        // if (auth()->user()->role === 'receptionist') {
        //     $cashStatus = [
        //         'today' => CashMovement::getCashStatusForDate($today),
        //         'unclosed_date' => CashMovement::hasUnclosedCash()
        //     ];
        // }

        $cashStatus = [
            'today' => CashMovement::getCashStatusForDate($today),
            'unclosed_date' => CashMovement::hasUnclosedCash(),
        ];

        // Consultas del día - Optimizado: 1 query en lugar de 5
        $consultasStats = Appointment::forDate($today)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "attended" THEN 1 ELSE 0 END) as completadas,
                SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as canceladas,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as ausentes
            ')
            ->first();

        $consultasHoy = [
            'total' => $consultasStats->total ?? 0,
            'completadas' => $consultasStats->completadas ?? 0,
            'pendientes' => $consultasStats->pendientes ?? 0,
            'canceladas' => $consultasStats->canceladas ?? 0,
            'ausentes' => $consultasStats->ausentes ?? 0,
        ];

        // Ingresos del día - Optimizado: 1 query SQL en lugar de 200+ operaciones
        $ingresosStats = DB::table('appointments')
            ->join('payment_appointments', 'appointments.id', '=', 'payment_appointments.appointment_id')
            ->join('payments', 'payment_appointments.payment_id', '=', 'payments.id')
            ->whereDate('appointments.appointment_date', $today)
            ->where('appointments.status', 'attended')
            ->selectRaw('
                COALESCE(SUM(payment_appointments.allocated_amount), 0) as total,
                COALESCE(SUM(CASE WHEN payments.payment_method = "cash" THEN payment_appointments.allocated_amount ELSE 0 END), 0) as efectivo,
                COALESCE(SUM(CASE WHEN payments.payment_method = "transfer" THEN payment_appointments.allocated_amount ELSE 0 END), 0) as transferencia,
                COALESCE(SUM(CASE WHEN payments.payment_method = "card" THEN payment_appointments.allocated_amount ELSE 0 END), 0) as tarjeta
            ')
            ->first();

        $ingresosHoy = [
            'total' => $ingresosStats->total ?? 0,
            'efectivo' => $ingresosStats->efectivo ?? 0,
            'transferencia' => $ingresosStats->transferencia ?? 0,
            'tarjeta' => $ingresosStats->tarjeta ?? 0,
        ];

        // Profesionales activos - Optimizado: 1 query en lugar de N queries
        $profesionalesStats = DB::table('professionals')
            ->where('is_active', true)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE
                    WHEN EXISTS (
                        SELECT 1 FROM appointments
                        WHERE appointments.professional_id = professionals.id
                        AND DATE(appointments.appointment_date) = ?
                        AND TIME(appointments.appointment_date) <= ?
                        AND TIME(appointments.appointment_date) > ?
                        AND appointments.status = "scheduled"
                    ) THEN 1 ELSE 0
                END) as enConsulta
            ', [$today->format('Y-m-d'), now()->format('H:i:s'), now()->subMinutes(60)->format('H:i:s')])
            ->first();

        $profesionalesActivos = [
            'total' => $profesionalesStats->total ?? 0,
            'enConsulta' => $profesionalesStats->enConsulta ?? 0,
            'disponibles' => ($profesionalesStats->total ?? 0) - ($profesionalesStats->enConsulta ?? 0),
        ];

        // Consultas detalladas del día - Optimizado: eager loading de paymentAppointments
        $consultasDetalle = Appointment::with(['patient', 'professional', 'paymentAppointments'])
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
                    'isPaid' => $appointment->paymentAppointments->isNotEmpty(),
                    'isUrgency' => $appointment->is_urgency,
                    'canMarkAttended' => $appointment->status === 'scheduled',
                    'canMarkCompleted' => $appointment->status === 'attended' && $appointment->paymentAppointments->isEmpty(),
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
                'id' => $prof->id,
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
            'cashStatus' => $cashStatus,
        ];

    return view('dashboard.dashboard', compact('dashboardData'));
    }

    public function appointments()
    {
        $today = Carbon::today();

        // Consultas detalladas del día (todas, sin filtros)
        $consultasDetalle = Appointment::with(['patient', 'professional', 'paymentAppointments.payment'])
            ->forDate($today)
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) {
                $isPaid = $appointment->paymentAppointments()->exists();
                $paymentId = null;

                if ($isPaid) {
                    $paymentId = $appointment->paymentAppointments->first()->payment_id ?? null;
                }

                return [
                    'id' => $appointment->id,
                    'paciente' => $appointment->patient->full_name,
                    'profesional' => $appointment->professional->full_name,
                    'hora' => $appointment->appointment_date->format('H:i'),
                    'monto' => $appointment->final_amount ?? $appointment->estimated_amount ?? 0,
                    'status' => $appointment->status,
                    'statusLabel' => $this->getStatusLabel($appointment->status),
                    'isPaid' => $isPaid,
                    'paymentId' => $paymentId,
                    'isUrgency' => $appointment->is_urgency,
                    'canMarkAttended' => $appointment->status === 'scheduled',
                    'canMarkCompleted' => $appointment->status === 'attended' && ! $isPaid,
                ];
            });

        $data = [
            'consultasDetalle' => $consultasDetalle->values(),
            'fecha' => $today->format('d/m/Y'),
        ];

    return view('dashboard.dashboard-appointments', compact('data'));
    }

    public function markAttended(Request $request, Appointment $appointment)
    {
        try {
            DB::beginTransaction();

            if ($appointment->status !== 'scheduled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden marcar como atendidos los turnos programados.',
                ], 400);
            }

            $appointment->update([
                'status' => 'attended',
            ]);

            // Intentar asignación automática de pago
            $paymentAssignment = $this->paymentAllocationService->checkAndAllocatePayment($appointment->id);

            // Si se asignó un pago automáticamente, actualizar el final_amount
            if ($paymentAssignment) {
                $appointment->update([
                    'final_amount' => $paymentAssignment->allocated_amount,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turno marcado como atendido exitosamente.',
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => 'attended',
                    'statusLabel' => 'Atendido',
                    'isPaid' => $appointment->fresh()->paymentAppointments()->exists(),
                    'canMarkAttended' => false,
                    'canMarkCompleted' => ! $appointment->fresh()->paymentAppointments()->exists(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al marcar el turno: '.$e->getMessage(),
            ], 500);
        }
    }

    public function markCompletedAndPaid(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'final_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,debit_card,credit_card',
            'concept' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            if ($appointment->status !== 'attended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cobrar turnos que han sido atendidos.',
                ], 400);
            }

            if ($appointment->paymentAppointments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este turno ya tiene un pago registrado.',
                ], 400);
            }

            // Validar que la caja esté abierta para procesar pagos
            $today = Carbon::today();
            $cashStatus = CashMovement::getCashStatusForDate($today);

            if ($cashStatus['is_closed']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden procesar pagos cuando la caja del día está cerrada. Debe abrir la caja para continuar.',
                ], 400);
            }

            if ($cashStatus['needs_opening']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden procesar pagos sin haber abierto la caja del día. Por favor, abra la caja primero.',
                ], 400);
            }

            // Actualizar monto final del turno
            $appointment->update([
                'final_amount' => $validated['final_amount'],
            ]);

            // Generar número de recibo
            $receiptNumber = $this->generateReceiptNumber();

            // Crear el pago individual
            $payment = Payment::create([
                'patient_id' => $appointment->patient_id,
                'payment_date' => now(),
                'payment_type' => 'single',
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['final_amount'],
                'sessions_included' => 1,
                'sessions_used' => 0, // El servicio lo marcará como usado después
                'liquidation_status' => 'pending',
                'concept' => $validated['concept'] ?: 'Pago de consulta - '.$appointment->patient->full_name,
                'receipt_number' => $receiptNumber,
            ]);

            // Asignar pago al turno usando el servicio
            $this->paymentAllocationService->allocateSinglePayment($payment->id, $appointment->id);

            // Registrar movimiento de caja
            $this->createCashMovement($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turno marcado como finalizado y cobrado exitosamente.',
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => 'attended',
                    'statusLabel' => 'Atendido',
                    'isPaid' => true,
                    'canMarkAttended' => false,
                    'canMarkCompleted' => false,
                    'monto' => $validated['final_amount'],
                ],
                'payment_id' => $payment->id,
                'receipt_number' => $receiptNumber,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: '.$e->getMessage(),
            ], 500);
        }
    }

    public function markAbsent(Request $request, Appointment $appointment)
    {
        try {
            if (! in_array($appointment->status, ['scheduled', 'attended'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este turno no se puede marcar como ausente.',
                ], 400);
            }

            $appointment->update([
                'status' => 'absent',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Turno marcado como ausente.',
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => 'absent',
                    'statusLabel' => 'Ausente',
                    'isPaid' => $appointment->paymentAppointments()->exists(),
                    'canMarkAttended' => false,
                    'canMarkCompleted' => false,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como ausente: '.$e->getMessage(),
            ], 500);
        }
    }

    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastPayment = Payment::whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastPayment && $lastPayment->receipt_number) {
            $lastNumber = intval(substr($lastPayment->receipt_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year.$month.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function createCashMovement(Payment $payment)
    {
        // Obtener balance actual con lock pesimista
        $currentBalance = CashMovement::getCurrentBalanceWithLock();
        $newBalance = $currentBalance + $payment->amount;

        CashMovement::create([
            'movement_date' => $payment->payment_date,
            'movement_type_id' => MovementType::getIdByCode('patient_payment'),
            'amount' => $payment->amount,
            'description' => $payment->concept ?: 'Pago de paciente - '.$payment->patient->full_name,
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'balance_after' => $newBalance,
            'user_id' => request()->user()->id,
        ]);
    }

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
