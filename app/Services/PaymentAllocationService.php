<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentAppointment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentAllocationService
{
    public function allocateSinglePayment(int $paymentId, int $appointmentId): PaymentAppointment
    {
        return DB::transaction(function () use ($paymentId, $appointmentId) {
            $payment = Payment::findOrFail($paymentId);
            $appointment = Appointment::findOrFail($appointmentId);

            $this->validateSinglePaymentAllocation($payment, $appointment);

            $paymentAppointment = PaymentAppointment::create([
                'payment_id' => $paymentId,
                'appointment_id' => $appointmentId,
                'professional_id' => $appointment->professional_id,
                'allocated_amount' => $payment->total_amount,
                'is_liquidation_trigger' => true,
            ]);

            return $paymentAppointment;
        });
    }

    public function allocatePackageSession(int $paymentId, int $appointmentId): PaymentAppointment
    {
        return DB::transaction(function () use ($paymentId, $appointmentId) {
            $payment = Payment::findOrFail($paymentId);
            $appointment = Appointment::findOrFail($appointmentId);

            // Buscar el paquete asociado al pago (nueva estructura v2.6.0)
            $patientPackage = \App\Models\PatientPackage::where('payment_id', $paymentId)->firstOrFail();

            $this->validatePackageSessionAllocation($payment, $appointment);

            $isFirstSession = $patientPackage->sessions_used == 0;
            $sessionAmount = $patientPackage->price_paid / $patientPackage->sessions_included;

            $paymentAppointment = PaymentAppointment::create([
                'payment_id' => $paymentId,
                'appointment_id' => $appointmentId,
                'professional_id' => $appointment->professional_id,
                'allocated_amount' => $sessionAmount,
                'is_liquidation_trigger' => $isFirstSession,
            ]);

            // Usar método del modelo PatientPackage para incrementar sesiones
            $patientPackage->useSession();

            // Si se completó el paquete, liquidar el pago
            if ($patientPackage->status === 'completed') {
                $payment->update(['liquidation_status' => 'liquidated']);
            }

            return $paymentAppointment;
        });
    }

    public function checkAndAllocatePayment(int $appointmentId): ?PaymentAppointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->status !== 'attended') {
            return null;
        }

        if ($appointment->paymentAppointments()->exists()) {
            return null;
        }

        // Buscar paquetes activos con sesiones disponibles para este paciente (nueva estructura v2.6.0)
        $availablePackage = \App\Models\PatientPackage::where('patient_id', $appointment->patient_id)
            ->where('status', 'active')
            ->whereColumn('sessions_used', '<', 'sessions_included')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($availablePackage) {
            // Usar el payment_id del paquete para asignar
            return $this->allocatePackageSession($availablePackage->payment_id, $appointmentId);
        }

        // Si no hay paquetes, buscar pagos individuales sin asignar
        $availableSinglePayment = Payment::where('patient_id', $appointment->patient_id)
            ->where('payment_type', 'single')
            ->where('status', 'confirmed')
            ->where('liquidation_status', '!=', 'cancelled')
            ->whereDoesntHave('paymentAppointments') // No asignado a ningún turno
            ->orderBy('created_at', 'asc')
            ->first();

        if ($availableSinglePayment) {
            return $this->allocateSinglePayment($availableSinglePayment->id, $appointmentId);
        }

        return null;
    }

    public function deallocatePayment(int $paymentAppointmentId): void
    {
        DB::transaction(function () use ($paymentAppointmentId) {
            $paymentAppointment = PaymentAppointment::findOrFail($paymentAppointmentId);
            $payment = $paymentAppointment->payment;

            // Si es un paquete, devolver la sesión
            if ($payment->payment_type === 'package_purchase') {
                $patientPackage = \App\Models\PatientPackage::where('payment_id', $payment->id)->first();

                if ($patientPackage) {
                    $patientPackage->returnSession();

                    // Si estaba liquidado y ahora tiene sesiones disponibles, volver a pending
                    if ($payment->liquidation_status === 'liquidated' && $patientPackage->sessions_remaining > 0) {
                        $payment->update(['liquidation_status' => 'pending']);
                    }
                }
            }

            $paymentAppointment->delete();
        });
    }

    public function getAvailablePackagesForPatient(int $patientId): \Illuminate\Database\Eloquent\Collection
    {
        // Retornar paquetes activos con sesiones disponibles (nueva estructura v2.6.0)
        return \App\Models\PatientPackage::where('patient_id', $patientId)
            ->where('status', 'active')
            ->whereColumn('sessions_used', '<', 'sessions_included')
            ->with(['patient', 'payment.paymentAppointments.appointment'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getPaymentAllocationSummary(int $paymentId): array
    {
        $payment = Payment::with(['paymentAppointments.appointment.professional', 'patientPackage'])
            ->findOrFail($paymentId);

        $allocatedAmount = $payment->paymentAppointments->sum('allocated_amount');
        $remainingAmount = $payment->total_amount - $allocatedAmount;

        // Si es un paquete, obtener info de sesiones del PatientPackage
        if ($payment->payment_type === 'package_purchase' && $payment->patientPackage) {
            $package = $payment->patientPackage;
            $totalSessions = $package->sessions_included;
            $usedSessions = $package->sessions_used;
            $remainingSessions = $package->sessions_remaining;
        } else {
            $totalSessions = 1;
            $usedSessions = $payment->paymentAppointments->count();
            $remainingSessions = max(0, 1 - $usedSessions);
        }

        return [
            'payment' => $payment,
            'total_sessions' => $totalSessions,
            'used_sessions' => $usedSessions,
            'remaining_sessions' => $remainingSessions,
            'total_amount' => $payment->total_amount,
            'allocated_amount' => $allocatedAmount,
            'remaining_amount' => $remainingAmount,
            'is_fully_allocated' => $remainingSessions <= 0,
            'appointments' => $payment->paymentAppointments->map(function ($pa) {
                return [
                    'id' => $pa->id,
                    'appointment_id' => $pa->appointment_id,
                    'date' => $pa->appointment->appointment_date,
                    'professional' => $pa->appointment->professional->full_name,
                    'amount' => $pa->allocated_amount,
                    'is_liquidation_trigger' => $pa->is_liquidation_trigger,
                ];
            }),
        ];
    }

    private function validateSinglePaymentAllocation(Payment $payment, Appointment $appointment): void
    {
        if ($payment->payment_type !== 'single') {
            throw new InvalidArgumentException('El pago debe ser de tipo individual (single)');
        }

        if ($appointment->status !== 'attended') {
            throw new RuntimeException('El turno debe estar marcado como asistido para asignar el pago');
        }

        if ($appointment->paymentAppointments()->exists()) {
            throw new RuntimeException('El turno ya tiene un pago asignado');
        }

        if ($payment->patient_id !== $appointment->patient_id) {
            throw new InvalidArgumentException('El pago y el turno deben pertenecer al mismo paciente');
        }

        if ($payment->paymentAppointments()->exists()) {
            throw new RuntimeException('El pago individual ya fue utilizado');
        }
    }

    private function validatePackageSessionAllocation(Payment $payment, Appointment $appointment): void
    {
        if ($payment->payment_type !== 'package_purchase') {
            throw new InvalidArgumentException('El pago debe ser de tipo paquete (package_purchase)');
        }

        if ($appointment->status !== 'attended') {
            throw new RuntimeException('El turno debe estar marcado como asistido para asignar la sesión');
        }

        if ($appointment->paymentAppointments()->exists()) {
            throw new RuntimeException('El turno ya tiene un pago asignado');
        }

        if ($payment->patient_id !== $appointment->patient_id) {
            throw new InvalidArgumentException('El pago y el turno deben pertenecer al mismo paciente');
        }

        // Verificar sesiones disponibles desde PatientPackage
        $patientPackage = \App\Models\PatientPackage::where('payment_id', $payment->id)->first();

        if (!$patientPackage) {
            throw new RuntimeException('No se encontró el paquete asociado al pago');
        }

        if ($patientPackage->sessions_remaining <= 0) {
            throw new RuntimeException('No quedan sesiones disponibles en este paquete');
        }

        if ($patientPackage->status !== 'active') {
            throw new RuntimeException('No se pueden usar sesiones de un paquete ' . $patientPackage->status);
        }
    }
}
