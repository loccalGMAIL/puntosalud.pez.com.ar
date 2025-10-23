<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ScheduleException;
use App\Services\PaymentAllocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    protected $paymentAllocationService;

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        $this->paymentAllocationService = $paymentAllocationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['professional.specialty', 'patient', 'office']);

        // Filtros de fecha (por defecto: hoy y próximos 7 días)
        $startDate = $request->get('start_date', today()->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->addDays(7)->format('Y-m-d'));

        $query->whereBetween('appointment_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);

        // Filtro por profesional
        if ($request->filled('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Búsqueda por paciente
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->get();

        // Datos para filtros y formularios
        $professionals = Professional::where('is_active', true)->with('specialty')->orderBy('last_name')->get();
        $patients = Patient::where('activo', true)->orderBy('last_name')->orderBy('first_name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        // Estadísticas
        $stats = [
            'total' => $appointments->count(),
            'scheduled' => $appointments->where('status', 'scheduled')->count(),
            'attended' => $appointments->where('status', 'attended')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'absent' => $appointments->where('status', 'absent')->count(),
        ];

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'appointments' => $appointments,
                'professionals' => $professionals,
                'patients' => $patients,
                'offices' => $offices,
                'stats' => $stats,
            ]);
        }

        return view('appointments.index', compact('appointments', 'professionals', 'patients', 'offices', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log temporal para debug
            \Log::info('Appointment creation attempt', $request->all());

            $validated = $request->validate([
                'professional_id' => 'required|exists:professionals,id',
                'patient_id' => 'required|exists:patients,id',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i',
                'duration' => 'required|integer|in:10,15,20,30,40,45,60,90,120',
                'office_id' => 'nullable|exists:offices,id',
                'notes' => 'nullable|string|max:500',
                'estimated_amount' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:scheduled,attended,cancelled,absent',
                // Campos de pago
                'pay_now' => 'nullable|in:true,false,1,0,"true","false","1","0"',
                'payment_type' => 'nullable|in:single,package',
                'payment_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|in:cash,transfer,debit_card,credit_card',
                'payment_concept' => 'nullable|string|max:500',
                // Campos de paquete
                'package_sessions' => 'nullable|integer|min:2|max:20',
                'session_price' => 'nullable|numeric|min:0',
            ], [
                'professional_id.required' => 'Debe seleccionar un profesional.',
                'professional_id.exists' => 'El profesional seleccionado no existe.',
                'patient_id.required' => 'Debe seleccionar un paciente.',
                'patient_id.exists' => 'El paciente seleccionado no existe.',
                'appointment_date.required' => 'La fecha es obligatoria.',
                'appointment_time.required' => 'La hora es obligatoria.',
                'duration.required' => 'La duración es obligatoria.',
            ]);

            // Validar que la caja esté abierta SOLO para pagos inmediatos
            $hasImmediatePayment = ! empty($validated['pay_now']) &&
                                 in_array($validated['pay_now'], ['true', 'True', '1', 1, true], true);

            // Solo validar caja si se va a cobrar inmediatamente
            if ($hasImmediatePayment) {
                $today = Carbon::today();
                $cashStatus = CashMovement::getCashStatusForDate($today);

                if ($cashStatus['is_closed']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden procesar pagos cuando la caja del día está cerrada. El turno puede crearse sin pago o debe abrir la caja.',
                        'error_type' => 'cash_closed',
                    ], 422);
                }

                if ($cashStatus['needs_opening']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden procesar pagos sin haber abierto la caja del día. El turno puede crearse sin pago o debe abrir la caja primero.',
                        'error_type' => 'cash_not_opened',
                    ], 422);
                }
            }

            // Limpiar campos opcionales vacíos
            if (empty($validated['office_id'])) {
                $validated['office_id'] = null;
            }
            if (empty($validated['notes'])) {
                $validated['notes'] = null;
            }
            if (empty($validated['estimated_amount'])) {
                $validated['estimated_amount'] = null;
            }

            // Limpiar campos de pago opcionales
            if (! isset($validated['payment_concept']) || empty($validated['payment_concept'])) {
                $validated['payment_concept'] = null;
            }
            if (! isset($validated['payment_amount']) || empty($validated['payment_amount'])) {
                $validated['payment_amount'] = null;
            }
            if (! isset($validated['package_sessions']) || empty($validated['package_sessions'])) {
                $validated['package_sessions'] = null;
            }
            if (! isset($validated['session_price']) || empty($validated['session_price'])) {
                $validated['session_price'] = null;
            }

            // Crear fecha y hora completa
            $appointmentDateTime = Carbon::parse($validated['appointment_date'].' '.$validated['appointment_time']);

            // Validar disponibilidad del profesional (incluyendo horarios de trabajo)
            $availabilityCheck = $this->checkProfessionalAvailability(
                $validated['professional_id'],
                $appointmentDateTime,
                $validated['duration']
            );

            if (! $availabilityCheck['available']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => ['appointment_time' => [$availabilityCheck['reason']]],
                    ], 422);
                }

                return redirect()->back()->withErrors(['appointment_time' => $availabilityCheck['reason']]);
            }

            DB::beginTransaction();

            // Crear turno
            $appointment = Appointment::create([
                'professional_id' => $validated['professional_id'],
                'patient_id' => $validated['patient_id'],
                'appointment_date' => $appointmentDateTime,
                'duration' => $validated['duration'],
                'office_id' => $validated['office_id'],
                'notes' => $validated['notes'],
                'estimated_amount' => $validated['estimated_amount'],
                'status' => 'scheduled',
            ]);

            // Si se paga ahora, crear el pago (pero no asignarlo hasta que se atienda)
            if ($request->boolean('pay_now') && $validated['payment_amount'] > 0) {
                $paymentType = $validated['payment_type'] ?? 'single';

                if ($paymentType === 'package') {
                    $this->createPackagePayment($appointment, $validated, false); // false = no asignar aún
                } else {
                    $this->createPrepayment($appointment, $validated, false); // false = no asignar aún
                }
            }

            DB::commit();

            $message = 'Turno creado exitosamente.';
            if ($request->boolean('pay_now')) {
                $message .= ' Pago registrado correctamente.';
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('appointments.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $e->errors(),
                    ], 422);
                }
                throw $e;
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el turno: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Error al crear el turno: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['professional.specialty', 'patient', 'office']);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Si es solo cambio de estado
        if ($request->has('status') && ! $request->has('professional_id')) {
            $updateData = ['status' => $request->status];

            if ($request->status === 'attended' && ! $appointment->final_amount) {
                $updateData['final_amount'] = $appointment->estimated_amount;
            }

            $appointment->update($updateData);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Estado del turno actualizado.']);
            }

            return redirect()->back()->with('success', 'Estado del turno actualizado.');
        }

        // Actualización completa
        try {
            $validated = $request->validate([
                'professional_id' => 'required|exists:professionals,id',
                'patient_id' => 'required|exists:patients,id',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|string',
                'duration' => 'required|integer|in:10,15,20,30,40,45,60,90,120',
                'office_id' => 'nullable|exists:offices,id',
                'notes' => 'nullable|string|max:500',
                'estimated_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:scheduled,attended,cancelled,absent',
            ]);

            // Limpiar campos opcionales vacíos
            if (empty($validated['office_id'])) {
                $validated['office_id'] = null;
            }
            if (empty($validated['notes'])) {
                $validated['notes'] = null;
            }
            if (empty($validated['estimated_amount'])) {
                $validated['estimated_amount'] = null;
            }

            $appointmentDateTime = Carbon::parse($validated['appointment_date'].' '.$validated['appointment_time']);

            // Validar disponibilidad si cambió la fecha/hora o profesional
            if ($appointment->appointment_date->format('Y-m-d H:i') !== $appointmentDateTime->format('Y-m-d H:i') ||
                $appointment->professional_id != $validated['professional_id']) {

                $availabilityCheck = $this->checkProfessionalAvailability(
                    $validated['professional_id'],
                    $appointmentDateTime,
                    $validated['duration'],
                    $appointment->id
                );

                if (! $availabilityCheck['available']) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error de validación',
                            'errors' => ['appointment_time' => [$availabilityCheck['reason']]],
                        ], 422);
                    }

                    return redirect()->back()->withErrors(['appointment_time' => $availabilityCheck['reason']]);
                }
            }

            $appointment->update([
                'professional_id' => $validated['professional_id'],
                'patient_id' => $validated['patient_id'],
                'appointment_date' => $appointmentDateTime,
                'duration' => $validated['duration'],
                'office_id' => $validated['office_id'],
                'notes' => $validated['notes'],
                'estimated_amount' => $validated['estimated_amount'],
                'status' => $validated['status'],
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Turno actualizado exitosamente.']);
            }

            return redirect()->route('appointments.index')->with('success', 'Turno actualizado exitosamente.');

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
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        if ($appointment->status !== 'scheduled') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar turnos programados.',
                ], 422);
            }

            return redirect()->back()->withErrors(['error' => 'Solo se pueden cancelar turnos programados.']);
        }

        $appointment->update(['status' => 'cancelled']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Turno cancelado exitosamente.',
            ]);
        }

        return redirect()->back()->with('success', 'Turno cancelado exitosamente.');
    }

    /**
     * Store a new urgency appointment (entreturno)
     */
    public function storeUrgency(Request $request)
    {
        try {
            $validated = $request->validate([
                'professional_id' => 'required|exists:professionals,id',
                'patient_id' => 'required|exists:patients,id',
                'estimated_amount' => 'required|numeric|min:0',
                'office_id' => 'nullable|exists:offices,id',
                'notes' => 'nullable|string|max:500',
            ], [
                'professional_id.required' => 'Debe seleccionar un profesional.',
                'professional_id.exists' => 'El profesional seleccionado no existe.',
                'patient_id.required' => 'Debe seleccionar un paciente.',
                'patient_id.exists' => 'El paciente seleccionado no existe.',
                'estimated_amount.required' => 'El monto es obligatorio.',
                'estimated_amount.numeric' => 'El monto debe ser un número válido.',
                'estimated_amount.min' => 'El monto debe ser mayor o igual a 0.',
            ]);

            // Limpiar campos opcionales vacíos
            if (empty($validated['office_id'])) {
                $validated['office_id'] = null;
            }
            if (empty($validated['notes'])) {
                $validated['notes'] = null;
            }

            DB::beginTransaction();

            // Crear turno de urgencia con duration = 0 y fecha/hora actual
            $appointment = Appointment::create([
                'professional_id' => $validated['professional_id'],
                'patient_id' => $validated['patient_id'],
                'appointment_date' => now(), // Fecha y hora actual
                'duration' => 0, // duration = 0 indica urgencia
                'office_id' => $validated['office_id'],
                'notes' => $validated['notes'],
                'estimated_amount' => $validated['estimated_amount'],
                'status' => 'scheduled',
            ]);

            DB::commit();

            $message = 'Urgencia registrada exitosamente.';

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('appointments.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $e->errors(),
                    ], 422);
                }
                throw $e;
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar la urgencia: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Error al registrar la urgencia: '.$e->getMessage()]);
        }
    }

    /**
     * Get available time slots for a professional on a specific date
     */
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'date' => 'required|date',
            'duration' => 'required|integer|min:10|max:120',
        ]);

        $slots = [];
        $date = Carbon::parse($validated['date']);

        // No generar slots para fechas pasadas o fines de semana
        if ($date->isPast() || $date->isWeekend()) {
            return response()->json($slots);
        }

        // Obtener turnos existentes del día
        $existingAppointments = Appointment::where('professional_id', $validated['professional_id'])
            ->whereDate('appointment_date', $date)
            ->where('status', 'scheduled')
            ->get();

        // Generar slots de 8:00 a 21:00 cada 30 minutos
        $currentTime = $date->copy()->setTime(8, 0);
        $endTime = $date->copy()->setTime(21, 0);
        $duration = (int) $validated['duration'];

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            // Verificar si el slot está libre
            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                if ($currentTime->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = $currentTime->format('H:i');
            }

            $currentTime->addMinutes(30);
        }

        return response()->json($slots);
    }

    /**
     * Crear pago de paquete/tratamiento
     */
    private function createPackagePayment(Appointment $appointment, array $validated, bool $assignImmediately = true)
    {
        // Generar número de recibo
        $receiptNumber = $this->generateReceiptNumber();

        // Crear el pago de paquete
        $payment = Payment::create([
            'patient_id' => $appointment->patient_id,
            'payment_date' => now(),
            'payment_type' => 'package', // ← Tipo paquete
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['payment_amount'],
            'sessions_included' => $validated['package_sessions'], // ← Sesiones del paquete
            'sessions_used' => 0, // ← Se irá incrementando con cada turno
            'liquidation_status' => 'pending',
            'concept' => ($validated['payment_concept'] ?? '') ?: 'Paquete '.$validated['package_sessions'].' sesiones - '.$appointment->patient->full_name,
            'receipt_number' => $receiptNumber,
            'created_by' => auth()->id(),
        ]);

        // Asignar la primera sesión al turno actual solo si se debe hacer inmediatamente
        if ($assignImmediately) {
            $this->paymentAllocationService->allocatePackageSession($payment->id, $appointment->id);
        }

        // Registrar movimiento de caja - TODO EL PAQUETE INGRESA HOY
        $this->createCashMovement($payment);
    }

    /**
     * Crear prepago para un turno individual
     */
    private function createPrepayment(Appointment $appointment, array $validated, bool $assignImmediately = true)
    {
        // Generar número de recibo
        $receiptNumber = $this->generateReceiptNumber();

        // Crear el pago
        $payment = Payment::create([
            'patient_id' => $appointment->patient_id,
            'payment_date' => now(),
            'payment_type' => 'single',
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['payment_amount'],
            'sessions_included' => 1,
            'sessions_used' => 0,
            'liquidation_status' => 'pending',
            'concept' => ($validated['payment_concept'] ?? '') ?: 'Pago anticipado - '.$appointment->patient->full_name,
            'receipt_number' => $receiptNumber,
            'created_by' => auth()->id(),
        ]);

        // Asignar pago al turno solo si se debe hacer inmediatamente
        if ($assignImmediately) {
            $this->paymentAllocationService->allocateSinglePayment($payment->id, $appointment->id);
        }

        // Registrar movimiento de caja - INGRESA INMEDIATAMENTE
        $this->createCashMovement($payment);
    }

    /**
     * Generar número de recibo
     */
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

    /**
     * Crear movimiento de caja
     */
    private function createCashMovement(Payment $payment)
    {
        // Obtener balance actual con lock pesimista
        $currentBalance = CashMovement::getCurrentBalanceWithLock();
        $newBalance = $currentBalance + $payment->amount;

        CashMovement::create([
            'movement_date' => $payment->payment_date,
            'type' => 'patient_payment',
            'amount' => $payment->amount,
            'description' => $payment->concept ?: 'Pago anticipado - '.$payment->patient->full_name,
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'balance_after' => $newBalance,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Verificar disponibilidad del profesional considerando horarios y turnos existentes
     */
    private function checkProfessionalAvailability($professionalId, $appointmentDateTime, $duration, $editingAppointmentId = null)
    {
        // Convertir duración a entero para evitar errores con addMinutes
        $duration = (int) $duration;
        // 1. Verificar que no sea una fecha/hora pasada
        if ($appointmentDateTime->isPast()) {
            return [
                'available' => false,
                'reason' => 'No se pueden crear turnos en fechas y horarios pasados.',
            ];
        }

        // 2. Verificar que no sea fin de semana
        if ($appointmentDateTime->isWeekend()) {
            return [
                'available' => false,
                'reason' => 'No se pueden crear turnos los fines de semana.',
            ];
        }

        // 3. Verificar excepciones de horario (días feriados/no laborables)
        $exception = ScheduleException::where('exception_date', $appointmentDateTime->toDateString())
            ->where(function ($query) {
                $query->where('affects_all', true);
            })
            ->first();

        if ($exception) {
            return [
                'available' => false,
                'reason' => 'Día no laborable: '.$exception->reason,
            ];
        }

        // 4. Verificar horarios del profesional
        $dayOfWeek = $appointmentDateTime->dayOfWeek;
        if ($dayOfWeek == 0) {
            $dayOfWeek = 7;
        } // Domingo = 7

        $schedule = ProfessionalSchedule::where('professional_id', $professionalId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return [
                'available' => false,
                'reason' => 'El profesional no trabaja este día de la semana.',
            ];
        }

        // 5. Verificar que la hora esté dentro del horario laboral
        $appointmentTime = $appointmentDateTime->format('H:i');
        $appointmentEndTime = $appointmentDateTime->copy()->addMinutes($duration)->format('H:i');
        $scheduleStart = $schedule->start_time->format('H:i');
        $scheduleEnd = $schedule->end_time->format('H:i');

        if ($appointmentTime < $scheduleStart || $appointmentEndTime > $scheduleEnd) {
            return [
                'available' => false,
                'reason' => 'El horario debe estar entre '.$scheduleStart.' y '.$scheduleEnd.'. '.
                    'Solicitado: '.$appointmentTime.' - '.$appointmentEndTime,
            ];
        }

        // 6. Verificar conflictos con turnos existentes
        $query = Appointment::where('professional_id', $professionalId)
            ->where('status', 'scheduled')
            ->where(function ($q) use ($appointmentDateTime, $duration) {
                $endDateTime = $appointmentDateTime->copy()->addMinutes($duration);

                $q->where(function ($subQuery) use ($appointmentDateTime, $endDateTime) {
                    // El nuevo turno empieza antes de que termine uno existente
                    $subQuery->where('appointment_date', '<', $endDateTime)
                        ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?', [$appointmentDateTime]);
                });
            });

        // Si estamos editando un turno, excluirlo de la verificación
        if ($editingAppointmentId) {
            $query->where('id', '!=', $editingAppointmentId);
        }

        $existingAppointment = $query->first();

        if ($existingAppointment) {
            return [
                'available' => false,
                'reason' => 'El profesional ya tiene un turno en ese horario.',
            ];
        }

        return [
            'available' => true,
            'reason' => null,
        ];
    }
}
