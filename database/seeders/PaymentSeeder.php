<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $adminUser = User::where('role', 'admin')->first();
        
        // Crear algunos pagos únicos
        $this->createSinglePayments($patients, $adminUser);
        
        // Crear algunos paquetes
        $this->createPackagePayments($patients, $adminUser);
        
        // Crear algunos reembolsos
        $this->createRefundPayments($patients, $adminUser);
        
        // Asociar pagos con citas atendidas
        $this->associatePaymentsWithAppointments();
    }

    private function createSinglePayments($patients, $adminUser)
    {
        for ($i = 0; $i < 20; $i++) {
            $patient = $patients->random();
            $paymentDate = Carbon::today()->subDays(rand(0, 30))->addHours(rand(8, 18));
            
            Payment::create([
                'patient_id' => $patient->id,
                'payment_date' => $paymentDate,
                'payment_type' => 'single',
                'payment_method' => ['cash', 'transfer', 'card'][rand(0, 2)],
                'amount' => rand(8000, 25000),
                'sessions_included' => 1,
                'sessions_used' => 0,
                'concept' => 'Consulta médica',
                'receipt_number' => 'REC-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'created_by' => $adminUser->id,
            ]);
        }
    }

    private function createPackagePayments($patients, $adminUser)
    {
        for ($i = 0; $i < 8; $i++) {
            $patient = $patients->random();
            $paymentDate = Carbon::today()->subDays(rand(0, 60))->addHours(rand(8, 18));
            $sessions = [4, 6, 8, 10][rand(0, 3)];
            $sessionsUsed = rand(0, min($sessions, 5));
            
            Payment::create([
                'patient_id' => $patient->id,
                'payment_date' => $paymentDate,
                'payment_type' => 'package',
                'payment_method' => ['cash', 'transfer', 'card'][rand(0, 2)],
                'amount' => $sessions * rand(8000, 12000), // Descuento por paquete
                'sessions_included' => $sessions,
                'sessions_used' => $sessionsUsed,
                'concept' => "Paquete de $sessions sesiones",
                'receipt_number' => 'PAQ-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'created_by' => $adminUser->id,
            ]);
        }
    }

    private function createRefundPayments($patients, $adminUser)
    {
        for ($i = 0; $i < 3; $i++) {
            $patient = $patients->random();
            $paymentDate = Carbon::today()->subDays(rand(0, 15))->addHours(rand(8, 18));
            
            Payment::create([
                'patient_id' => $patient->id,
                'payment_date' => $paymentDate,
                'payment_type' => 'refund',
                'payment_method' => ['cash', 'transfer'][rand(0, 1)],
                'amount' => -rand(5000, 15000), // Monto negativo para reembolso
                'sessions_included' => 0,
                'sessions_used' => 0,
                'concept' => 'Reembolso por cancelación',
                'receipt_number' => 'REF-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'created_by' => $adminUser->id,
            ]);
        }
    }

    private function associatePaymentsWithAppointments()
    {
        // Obtener citas atendidas sin pagos asociados
        $attendedAppointments = Appointment::where('status', 'attended')
            ->whereDoesntHave('paymentAppointments')
            ->with('patient')
            ->get();

        foreach ($attendedAppointments as $appointment) {
            // Buscar un pago del paciente que pueda cubrir esta cita
            $availablePayments = Payment::where('patient_id', $appointment->patient_id)
                ->where('amount', '>', 0) // Solo pagos positivos
                ->where(function ($query) {
                    $query->where('payment_type', 'single')
                        ->orWhere(function ($q) {
                            $q->where('payment_type', 'package')
                              ->whereRaw('sessions_used < sessions_included');
                        });
                })
                ->get();

            if ($availablePayments->isNotEmpty()) {
                $payment = $availablePayments->random();
                $allocatedAmount = $appointment->final_amount ?? rand(8000, 20000);

                // Crear la asociación
                $payment->paymentAppointments()->create([
                    'appointment_id' => $appointment->id,
                    'allocated_amount' => $allocatedAmount,
                    'is_liquidation_trigger' => rand(0, 1) == 1,
                ]);

                // Si es un paquete, incrementar sesiones usadas
                if ($payment->payment_type === 'package') {
                    $payment->increment('sessions_used');
                }
            }
        }
    }
}