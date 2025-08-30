<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\PaymentAppointment;
use App\Models\CashMovement;
use App\Services\PaymentAllocationService;
use Carbon\Carbon;
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
        $query = Payment::with(['patient', 'paymentAppointments.appointment.professional']);
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            })->orWhere('receipt_number', 'like', "%{$search}%");
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
                'stats' => $stats
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
            'payment_method' => 'required|in:cash,transfer,card',
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
            if ($validated['payment_type'] === 'single' && !empty($validated['appointment_ids'])) {
                foreach ($validated['appointment_ids'] as $appointmentId) {
                    $this->paymentAllocationService->allocateSinglePayment($payment->id, $appointmentId);
                }
            }
            
            // Si es reembolso, crear las relaciones manualmente
            if ($validated['payment_type'] === 'refund' && !empty($validated['appointment_ids'])) {
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
                    'payment' => $payment->load(['patient', 'paymentAppointments.appointment'])
                ]);
            }
            
            return redirect()->route('payments.show', $payment)
                ->with('success', 'Pago registrado exitosamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el pago: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    public function show(Payment $payment)
    {
        $payment->load([
            'patient',
            'paymentAppointments.appointment.professional',
            'paymentAppointments.appointment.office'
        ]);
        
        return view('payments.show', compact('payment'));
    }
    
    public function edit(Payment $payment)
    {
        $payment->load([
            'patient',
            'paymentAppointments.appointment.professional'
        ]);
        
        return view('payments.edit', compact('payment'));
    }
    
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer,card',
            'amount' => 'required|numeric|min:0',
            'concept' => 'nullable|string|max:500',
            'liquidation_status' => 'required|in:pending,liquidated,cancelled',
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldAmount = $payment->amount;
            $oldMethod = $payment->payment_method;
            
            $payment->update($validated);
            
            // Si cambió el monto o método, actualizar movimiento de caja
            if ($oldAmount != $validated['amount'] || $oldMethod != $validated['payment_method']) {
                $this->updateCashMovement($payment, $oldAmount, $oldMethod);
            }
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago actualizado exitosamente.',
                    'payment' => $payment->fresh()
                ]);
            }
            
            return redirect()->route('payments.show', $payment)
                ->with('success', 'Pago actualizado exitosamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el pago: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar el pago: ' . $e->getMessage()]);
        }
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
                    'message' => 'Pago eliminado exitosamente.'
                ]);
            }
            
            return redirect()->route('payments.index')
                ->with('success', 'Pago eliminado exitosamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el pago: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar el pago: ' . $e->getMessage()]);
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
                'payment_appointment' => $paymentAppointment
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
                'payment_appointment' => $paymentAppointment
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function deallocatePayment(Request $request, PaymentAppointment $paymentAppointment)
    {
        try {
            $this->paymentAllocationService->deallocatePayment($paymentAppointment->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Asignación de pago eliminada exitosamente.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function getAvailablePackages(Patient $patient)
    {
        $packages = $this->paymentAllocationService->getAvailablePackagesForPatient($patient->id);
        
        return response()->json([
            'success' => true,
            'packages' => $packages
        ]);
    }
    
    public function getPaymentAllocationSummary(Payment $payment)
    {
        $summary = $this->paymentAllocationService->getPaymentAllocationSummary($payment->id);
        
        return response()->json([
            'success' => true,
            'summary' => $summary
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
                    'payment_appointment' => $paymentAppointment
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
                'message' => $e->getMessage()
            ], 400);
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
        
        return $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    private function createCashMovement(Payment $payment)
    {
        $multiplier = $payment->payment_type === 'refund' ? -1 : 1;
        
        CashMovement::create([
            'movement_date' => $payment->payment_date,
            'movement_type' => $payment->payment_type === 'refund' ? 'outflow' : 'inflow',
            'amount' => $payment->amount * $multiplier,
            'payment_method' => $payment->payment_method,
            'concept' => $payment->concept ?: $this->getDefaultConcept($payment),
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'user_id' => auth()->id(),
        ]);
    }
    
    private function updateCashMovement(Payment $payment, $oldAmount, $oldMethod)
    {
        $cashMovement = CashMovement::where('reference_type', 'payment')
            ->where('reference_id', $payment->id)
            ->first();
        
        if ($cashMovement) {
            $multiplier = $payment->payment_type === 'refund' ? -1 : 1;
            
            $cashMovement->update([
                'amount' => $payment->amount * $multiplier,
                'payment_method' => $payment->payment_method,
                'concept' => $payment->concept ?: $this->getDefaultConcept($payment),
            ]);
        }
    }
    
    private function reverseCashMovement(Payment $payment)
    {
        CashMovement::where('reference_type', 'payment')
            ->where('reference_id', $payment->id)
            ->delete();
    }
    
    private function getDefaultConcept(Payment $payment)
    {
        $concepts = [
            'single' => 'Pago individual - ' . $payment->patient->full_name,
            'package' => 'Paquete de sesiones - ' . $payment->patient->full_name,
            'refund' => 'Reembolso - ' . $payment->patient->full_name,
        ];
        
        return $concepts[$payment->payment_type] ?? 'Pago - ' . $payment->patient->full_name;
    }
}