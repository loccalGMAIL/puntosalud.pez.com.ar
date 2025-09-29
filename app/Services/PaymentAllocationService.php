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
                'allocated_amount' => $payment->amount,
                'is_liquidation_trigger' => true,
            ]);

            $payment->update(['sessions_used' => 1]);

            return $paymentAppointment;
        });
    }

    public function allocatePackageSession(int $paymentId, int $appointmentId): PaymentAppointment
    {
        return DB::transaction(function () use ($paymentId, $appointmentId) {
            $payment = Payment::findOrFail($paymentId);
            $appointment = Appointment::findOrFail($appointmentId);

            $this->validatePackageSessionAllocation($payment, $appointment);

            $isFirstSession = $payment->sessions_used == 0;
            $sessionAmount = $payment->amount / $payment->sessions_included;

            $paymentAppointment = PaymentAppointment::create([
                'payment_id' => $paymentId,
                'appointment_id' => $appointmentId,
                'allocated_amount' => $sessionAmount,
                'is_liquidation_trigger' => $isFirstSession,
            ]);

            $newSessionsUsed = $payment->sessions_used + 1;
            $updateData = ['sessions_used' => $newSessionsUsed];

            if ($newSessionsUsed >= $payment->sessions_included) {
                $updateData['liquidation_status'] = 'liquidated';
            }

            $payment->update($updateData);

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

        // Buscar paquetes disponibles para este paciente
        $availablePackage = Payment::where('patient_id', $appointment->patient_id)
            ->where('payment_type', 'package')
            ->where('liquidation_status', '!=', 'cancelled')
            ->whereColumn('sessions_used', '<', 'sessions_included')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($availablePackage) {
            return $this->allocatePackageSession($availablePackage->id, $appointmentId);
        }

        // Si no hay paquetes, buscar pagos individuales sin asignar
        $availableSinglePayment = Payment::where('patient_id', $appointment->patient_id)
            ->where('payment_type', 'single')
            ->where('liquidation_status', '!=', 'cancelled')
            ->where('sessions_used', 0) // No usado aún
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

            if ($payment->payment_type === 'single') {
                $payment->update(['sessions_used' => 0]);
            } else {
                $newSessionsUsed = max(0, $payment->sessions_used - 1);
                $updateData = ['sessions_used' => $newSessionsUsed];

                if ($payment->liquidation_status === 'liquidated' && $newSessionsUsed < $payment->sessions_included) {
                    $updateData['liquidation_status'] = 'pending';
                }

                $payment->update($updateData);
            }

            $paymentAppointment->delete();
        });
    }

    public function getAvailablePackagesForPatient(int $patientId): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::where('patient_id', $patientId)
            ->where('payment_type', 'package')
            ->where('liquidation_status', '!=', 'cancelled')
            ->whereColumn('sessions_used', '<', 'sessions_included')
            ->with(['patient', 'paymentAppointments.appointment'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getPaymentAllocationSummary(int $paymentId): array
    {
        $payment = Payment::with(['paymentAppointments.appointment.professional'])
            ->findOrFail($paymentId);

        $allocatedAmount = $payment->paymentAppointments->sum('allocated_amount');
        $remainingSessions = $payment->sessions_included - $payment->sessions_used;
        $remainingAmount = $payment->amount - $allocatedAmount;

        return [
            'payment' => $payment,
            'total_sessions' => $payment->sessions_included,
            'used_sessions' => $payment->sessions_used,
            'remaining_sessions' => $remainingSessions,
            'total_amount' => $payment->amount,
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

        if ($payment->sessions_used > 0) {
            throw new RuntimeException('El pago individual ya fue utilizado');
        }
    }

    private function validatePackageSessionAllocation(Payment $payment, Appointment $appointment): void
    {
        if ($payment->payment_type !== 'package') {
            throw new InvalidArgumentException('El pago debe ser de tipo paquete (package)');
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

        if ($payment->sessions_used >= $payment->sessions_included) {
            throw new RuntimeException('No quedan sesiones disponibles en este paquete');
        }

        if ($payment->liquidation_status === 'cancelled') {
            throw new RuntimeException('No se pueden usar sesiones de un paquete cancelado');
        }
    }
}
