<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\MovementType;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentAppointment;
use App\Services\PaymentAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentAllocationService;

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        $this->paymentAllocationService = $paymentAllocationService;
    }

    public function index(Request $request)
    {
        // Obtener todos los payments (ahora incluye pagos de pacientes e ingresos manuales)
        $query = Payment::with(['patient', 'paymentAppointments.appointment.professional', 'createdBy']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function ($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%");
                })
                ->orWhere('receipt_number', 'like', "%{$search}%")
                ->orWhere('concept', 'like', "%{$search}%"); // Para buscar en ingresos manuales
            });
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('liquidation_status')) {
            $query->where('liquidation_status', $request->liquidation_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        // Estadísticas
        $stats = [
            'total' => Payment::count(),
            'total_amount' => Payment::sum('amount'),
            'total_transfers' => Payment::where('payment_method', 'transfer')->count(),
            'total_cash' => Payment::where('payment_method', 'cash')->count(),
            'pending_liquidation' => Payment::where('liquidation_status', 'pending')->count(),
            'liquidated' => Payment::where('liquidation_status', 'liquidated')->count(),
        ];

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'payments' => $payments,
                'stats' => $stats,
            ]);
        }

        return view('payments.index', compact('payments', 'stats'));
    }

    public function create(Request $request)
    {
        $selectedPatient = null;
        $pendingAppointments = collect();

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
            if ($selectedPatient) {
                // Obtener turnos pendientes de pago para este paciente
                $pendingAppointments = Appointment::with(['professional', 'office'])
                    ->where('patient_id', $selectedPatient->id)
                    ->where('status', 'attended')
                    ->whereDoesntHave('paymentAppointments')
                    ->orderBy('appointment_date', 'desc')
                    ->get();
            }
        }

        return view('payments.create', compact('selectedPatient', 'pendingAppointments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'payment_type' => 'required|in:single,package,refund',
            'payment_method' => 'required|in:cash,transfer,debit_card,credit_card',
            'amount' => 'required|numeric|min:0',
            'concept' => 'nullable|string|max:500',
            'sessions_included' => 'required_if:payment_type,package|nullable|integer|min:1',
            'appointment_ids' => 'nullable|array',
            'appointment_ids.*' => 'exists:appointments,id',
            'allocated_amounts' => 'nullable|array',
            'allocated_amounts.*' => 'numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generar número de recibo
            $receiptNumber = $this->generateReceiptNumber();

            // Crear el pago
            $payment = Payment::create([
                'patient_id' => $validated['patient_id'],
                'payment_date' => now(),
                'payment_type' => $validated['payment_type'],
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'sessions_included' => $validated['sessions_included'] ?? null,
                'sessions_used' => 0,
                'liquidation_status' => 'pending',
                'concept' => $validated['concept'],
                'receipt_number' => $receiptNumber,
            ]);

            // Si es pago individual, asignar usando el servicio
            if ($validated['payment_type'] === 'single' && ! empty($validated['appointment_ids'])) {
                foreach ($validated['appointment_ids'] as $appointmentId) {
                    $this->paymentAllocationService->allocateSinglePayment($payment->id, $appointmentId);
                }
            }

            // Si es reembolso, crear las relaciones manualmente
            if ($validated['payment_type'] === 'refund' && ! empty($validated['appointment_ids'])) {
                foreach ($validated['appointment_ids'] as $index => $appointmentId) {
                    $allocatedAmount = $validated['allocated_amounts'][$index] ?? 0;

                    PaymentAppointment::create([
                        'payment_id' => $payment->id,
                        'appointment_id' => $appointmentId,
                        'allocated_amount' => $allocatedAmount,
                        'is_liquidation_trigger' => true,
                    ]);
                }
            }

            // Registrar movimiento de caja
            $this->createCashMovement($payment);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago registrado exitosamente.',
                    'payment' => $payment->load(['patient', 'paymentAppointments.appointment']),
                ]);
            }

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Pago registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el pago: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el pago: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function show(Payment $payment, Request $request)
    {
        $payment->load([
            'patient',
            'paymentAppointments.appointment.professional',
            'paymentAppointments.appointment.office',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payment' => $payment,
            ]);
        }

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        // Deshabilitado: No se permite editar pagos para mantener integridad contable
        // Si hay un error, usar retiros/ingresos manuales para ajustes
        abort(403, 'No se permite editar pagos registrados. Para correcciones use retiros o ingresos manuales en caja.');
    }

    public function update(Request $request, Payment $payment)
    {
        // Deshabilitado: No se permite editar pagos para mantener integridad contable
        // Si hay un error, usar retiros/ingresos manuales para ajustes
        abort(403, 'No se permite editar pagos registrados. Para correcciones use retiros o ingresos manuales en caja.');
    }

    public function destroy(Payment $payment)
    {
        try {
            DB::beginTransaction();

            // Verificar que no esté liquidado
            if ($payment->liquidation_status === 'liquidated') {
                throw new \Exception('No se puede eliminar un pago que ya fue liquidado.');
            }

            // Eliminar relaciones con turnos
            $payment->paymentAppointments()->delete();

            // Revertir movimiento de caja
            $this->reverseCashMovement($payment);

            // Eliminar el pago
            $payment->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago eliminado exitosamente.',
                ]);
            }

            return redirect()->route('payments.index')
                ->with('success', 'Pago eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el pago: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar el pago: '.$e->getMessage()]);
        }
    }

    // Métodos para búsqueda AJAX
    public function searchPatients(Request $request)
    {
        $search = $request->get('q', '');

        $patients = Patient::where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('dni', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'dni']);

        return response()->json($patients);
    }

    public function getPendingAppointments(Request $request, Patient $patient)
    {
        $appointments = Appointment::with(['professional', 'office'])
            ->where('patient_id', $patient->id)
            ->where('status', 'attended')
            ->whereDoesntHave('paymentAppointments')
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json($appointments);
    }

    public function usePackageSession(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        try {
            $paymentAppointment = $this->paymentAllocationService->allocatePackageSession(
                $payment->id,
                $validated['appointment_id']
            );

            $payment->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Sesión del paquete utilizada exitosamente.',
                'remaining_sessions' => $payment->sessions_included - $payment->sessions_used,
                'payment_appointment' => $paymentAppointment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function allocateSinglePayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        try {
            $paymentAppointment = $this->paymentAllocationService->allocateSinglePayment(
                $payment->id,
                $validated['appointment_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Pago individual asignado exitosamente.',
                'payment_appointment' => $paymentAppointment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function deallocatePayment(Request $request, PaymentAppointment $paymentAppointment)
    {
        try {
            $this->paymentAllocationService->deallocatePayment($paymentAppointment->id);

            return response()->json([
                'success' => true,
                'message' => 'Asignación de pago eliminada exitosamente.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getAvailablePackages(Patient $patient)
    {
        $packages = $this->paymentAllocationService->getAvailablePackagesForPatient($patient->id);

        return response()->json([
            'success' => true,
            'packages' => $packages,
        ]);
    }

    public function getPaymentAllocationSummary(Payment $payment)
    {
        $summary = $this->paymentAllocationService->getPaymentAllocationSummary($payment->id);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function autoAllocatePayment(Appointment $appointment)
    {
        try {
            $paymentAppointment = $this->paymentAllocationService->checkAndAllocatePayment($appointment->id);

            if ($paymentAppointment) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago asignado automáticamente.',
                    'payment_appointment' => $paymentAppointment,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron paquetes disponibles para este turno.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function printReceipt(Payment $payment)
    {
        // Cargar relaciones necesarias
        $payment->load(['patient', 'paymentAppointments.appointment.professional.specialty']);

        // Obtener profesionales únicos asociados al pago
        $professionals = $payment->paymentAppointments
            ->map(fn($pa) => $pa->appointment->professional)
            ->unique('id')
            ->values();

        return view('receipts.print', compact('payment', 'professionals'));
    }

    /**
     * Anular un pago realizado
     * Crea un pago negativo (refund) y revierte el estado del turno
     */
    public function annul(Payment $payment)
    {
        try {
            DB::beginTransaction();

            // Verificar que la caja esté abierta
            if (! CashMovement::isCashOpenToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden anular pagos. La caja debe estar abierta para realizar esta operación.',
                ], 400);
            }

            // Verificar si ya fue anulado
            $existingRefund = Payment::where('concept', 'LIKE', '%#' . $payment->receipt_number . '%')
                ->where('payment_type', 'refund')
                ->first();

            if ($existingRefund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pago ya fue anulado anteriormente.',
                ], 400);
            }

            // Verificar que no sea un refund
            if ($payment->payment_type === 'refund') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede anular un reembolso. Solo se pueden anular pagos originales.',
                ], 400);
            }

            // Verificar que el pago esté pendiente de liquidación
            if ($payment->liquidation_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden anular pagos que estén pendientes de liquidación. Este pago ya fue ' .
                                ($payment->liquidation_status === 'liquidated' ? 'liquidado' : 'cancelado') . '.',
                ], 400);
            }

            // Crear pago negativo (refund)
            $refund = Payment::create([
                'patient_id' => $payment->patient_id,
                'payment_date' => now(),
                'payment_type' => 'refund',
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount, // El monto positivo, createCashMovement lo hace negativo
                'sessions_included' => 0,
                'sessions_used' => 0,
                'liquidation_status' => 'not_applicable', // Los refunds no se liquidan
                'concept' => 'Anulación de pago - Recibo #' . $payment->receipt_number,
                'receipt_number' => $this->generateReceiptNumber(),
                'created_by' => auth()->id(),
            ]);

            // Registrar movimiento de caja negativo
            $this->createCashMovement($refund);

            // Revertir turnos asociados al pago
            $appointments = $payment->paymentAppointments;

            foreach ($appointments as $paymentAppointment) {
                $appointment = $paymentAppointment->appointment;

                if ($appointment) {
                    // Eliminar la relación payment_appointment
                    $paymentAppointment->delete();

                    // Resetear el turno para que pueda ser cobrado nuevamente
                    $appointment->update([
                        'final_amount' => null,
                    ]);
                }
            }

            // Marcar el pago original como anulado
            $payment->update([
                'liquidation_status' => 'cancelled', // Cambiar a cancelado para que no quede pendiente de liquidación
                'concept' => ($payment->concept ? $payment->concept . ' ' : '') . '[ANULADO - Ref: ' . $refund->receipt_number . ']',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago anulado exitosamente. Se registró el movimiento de caja y los turnos fueron liberados para nuevo cobro.',
                'refund_receipt' => $refund->receipt_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al anular el pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Métodos privados
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
        // Verificar que la caja esté abierta
        if (! CashMovement::isCashOpenToday()) {
            throw new \Exception('No se pueden registrar pagos. La caja debe estar abierta para realizar esta operación.');
        }

        // Obtener balance actual con lock pesimista
        $currentBalance = CashMovement::getCurrentBalanceWithLock();

        // Determinar tipo y monto según si es reembolso o pago
        $movementTypeCode = $payment->payment_type === 'refund' ? 'refund' : 'patient_payment';
        $amount = $payment->payment_type === 'refund' ? -$payment->amount : $payment->amount;
        $newBalance = $currentBalance + $amount;

        // Generar descripción del movimiento
        $description = $payment->concept ?: $this->getDefaultConcept($payment);

        CashMovement::create([
            'movement_type_id' => MovementType::getIdByCode($movementTypeCode),
            'amount' => $amount,
            'description' => $description,
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'balance_after' => $newBalance,
            'user_id' => auth()->id(),
        ]);
    }

    // Métodos removidos: updateCashMovement() y reverseCashMovement()
    // Ya no se permite editar pagos para mantener integridad contable

    private function getDefaultConcept(Payment $payment)
    {
        $concepts = [
            'single' => 'Pago individual - '.$payment->patient->full_name,
            'package' => 'Paquete de sesiones - '.$payment->patient->full_name,
            'refund' => 'Reembolso - '.$payment->patient->full_name,
        ];

        return $concepts[$payment->payment_type] ?? 'Pago - '.$payment->patient->full_name;
    }
}
