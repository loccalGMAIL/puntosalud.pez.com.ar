<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\LiquidationDetail;
use App\Models\MovementType;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\ProfessionalLiquidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiquidationController extends Controller
{
    public function processLiquidation(Request $request)
    {
        $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $professional = Professional::findOrFail($request->professional_id);
            $amount = $request->amount;
            $date = Carbon::parse($request->date);

            // 1. Verificar que NO exista ya una liquidación para este profesional en esta fecha
            $existingLiquidation = ProfessionalLiquidation::where('professional_id', $professional->id)
                ->whereDate('liquidation_date', $date)
                ->first();

            if ($existingLiquidation) {
                throw new \Exception("Ya existe una liquidación para {$professional->full_name} en la fecha {$date->format('d/m/Y')}. ".
                                   "ID de liquidación: {$existingLiquidation->id}. ".
                                   "No se permite liquidar dos veces el mismo día.");
            }

            // 2. Verificar que la caja esté abierta
            $cashStatus = CashMovement::getCashStatusForDate($date);
            if (! $cashStatus['is_open']) {
                throw new \Exception('La caja debe estar abierta para procesar liquidaciones.');
            }

            // 3. Verificar turnos pendientes del profesional
            $pendingAppointments = Appointment::where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->where('status', 'scheduled')
                ->count();

            if ($pendingAppointments > 0) {
                throw new \Exception("No se puede liquidar. El profesional tiene {$pendingAppointments} ".
                                   ($pendingAppointments === 1 ? 'turno pendiente' : 'turnos pendientes').
                                   " sin atender del día {$date->format('d/m/Y')}.");
            }

            // 4. Verificar turnos atendidos sin cobrar
            $unpaidAppointments = Appointment::where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->where('status', 'attended')
                ->whereDoesntHave('paymentAppointments')
                ->count();

            if ($unpaidAppointments > 0) {
                throw new \Exception("No se puede liquidar. El profesional tiene {$unpaidAppointments} ".
                                   ($unpaidAppointments === 1 ? 'turno atendido' : 'turnos atendidos').
                                   " sin cobrar del día {$date->format('d/m/Y')}.");
            }

            // 5. Obtener todos los turnos atendidos del día con sus pagos
            $attendedAppointments = Appointment::with(['paymentAppointments.payment'])
                ->where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->where('status', 'attended')
                ->get();

            if ($attendedAppointments->isEmpty()) {
                throw new \Exception("No hay turnos atendidos para liquidar en la fecha {$date->format('d/m/Y')}.");
            }

            // 6. Validar que ningún pago ya esté liquidado
            $alreadyLiquidatedPayments = [];
            foreach ($attendedAppointments as $appointment) {
                foreach ($appointment->paymentAppointments as $pa) {
                    if ($pa->payment && $pa->payment->liquidation_status === 'liquidated') {
                        $alreadyLiquidatedPayments[] = $pa->payment->id;
                    }
                }
            }

            if (!empty($alreadyLiquidatedPayments)) {
                throw new \Exception("Algunos pagos ya fueron liquidados anteriormente. IDs: ".implode(', ', $alreadyLiquidatedPayments).". ".
                                   "No se puede liquidar dos veces el mismo pago.");
            }

            // 7. Calcular estadísticas y montos
            $totalAppointments = Appointment::where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->count();

            $absentAppointments = Appointment::where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->where('status', 'absent')
                ->count();

            $totalCollected = $attendedAppointments->sum('final_amount');
            $professionalCommission = $professional->calculateCommission($totalCollected);

            // Obtener reintegros del día para este profesional (usando referencias polimórficas)
            $refunds = CashMovement::byType('expense')
                ->where('reference_type', 'App\Models\Professional')
                ->where('reference_id', $professional->id)
                ->whereDate('created_at', $date)
                ->get();

            $totalRefunds = $refunds->sum(function($refund) {
                return abs($refund->amount); // Los gastos son negativos, convertir a positivo
            });

            $finalProfessionalAmount = $professionalCommission - $totalRefunds;
            $clinicAmount = $totalCollected - $professionalCommission;

            // Verificar que el monto ingresado coincida con la comisión calculada MENOS los reintegros
            if (abs($amount - $finalProfessionalAmount) > 0.01) {
                throw new \Exception("El monto ingresado (\${$amount}) no coincide con la comisión calculada menos reintegros (\${$finalProfessionalAmount}). ".
                                   "Comisión base: \${$professionalCommission} - Reintegros: \${$totalRefunds} = \${$finalProfessionalAmount}");
            }

            // 8. Verificar que hay suficiente efectivo en caja
            $currentBalance = $this->getCurrentCashBalance($date);
            if ($currentBalance < $amount) {
                throw new \Exception('Saldo insuficiente en caja. Disponible: $'.number_format($currentBalance, 2));
            }

            // 9. Crear registro en professional_liquidations
            $liquidation = ProfessionalLiquidation::create([
                'professional_id' => $professional->id,
                'liquidation_date' => $date,
                'sheet_type' => 'liquidation',
                'appointments_total' => $totalAppointments,
                'appointments_attended' => $attendedAppointments->count(),
                'appointments_absent' => $absentAppointments,
                'total_collected' => $totalCollected,
                'professional_commission' => $professionalCommission,
                'clinic_amount' => $clinicAmount,
                'payment_status' => 'paid', // Se marca como pagado inmediatamente
                'payment_method' => 'cash',
                'paid_at' => now(),
                'paid_by' => auth()->id(),
                'notes' => "Liquidación procesada el {$date->format('d/m/Y')}".
                          ($totalRefunds > 0 ? " - Reintegros descontados: \${$totalRefunds}" : ""),
            ]);

            // 10. Crear detalles en liquidation_details por cada turno
            $paymentIds = [];
            foreach ($attendedAppointments as $appointment) {
                $paymentAppointment = $appointment->paymentAppointments->first();
                $payment = $paymentAppointment ? $paymentAppointment->payment : null;

                $appointmentAmount = $appointment->final_amount ?? 0;
                $appointmentCommission = $professional->calculateCommission($appointmentAmount);

                LiquidationDetail::create([
                    'liquidation_id' => $liquidation->id,
                    'payment_appointment_id' => $paymentAppointment?->id,
                    'payment_id' => $payment?->id,
                    'appointment_id' => $appointment->id,
                    'amount' => $appointmentAmount,
                    'commission_amount' => $appointmentCommission,
                    'concept' => "Turno {$appointment->appointment_date->format('H:i')} - {$appointment->patient->full_name}",
                ]);

                if ($payment) {
                    $paymentIds[] = $payment->id;
                }
            }

            // 11. Actualizar liquidation_status en payments
            if (!empty($paymentIds)) {
                Payment::whereIn('id', $paymentIds)
                    ->update(['liquidation_status' => 'liquidated']);
            }

            // 12. Crear movimiento de caja por pago al profesional
            CashMovement::create([
                'movement_type_id' => MovementType::getIdByCode('professional_payment'),
                'amount' => -$amount, // Negativo porque es una salida de dinero
                'description' => "Liquidación profesional: {$professional->full_name} - {$attendedAppointments->count()} turnos",
                'reference_type' => ProfessionalLiquidation::class,
                'reference_id' => $liquidation->id,
                'balance_after' => $currentBalance - $amount,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Liquidación procesada correctamente',
                'data' => [
                    'liquidation_id' => $liquidation->id,
                    'professional_name' => $professional->full_name,
                    'appointments_attended' => $attendedAppointments->count(),
                    'total_collected' => $totalCollected,
                    'professional_commission' => $professionalCommission,
                    'total_refunds' => $totalRefunds,
                    'final_professional_amount' => $finalProfessionalAmount,
                    'clinic_amount' => $clinicAmount,
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'new_balance' => $currentBalance - $amount,
                    'payments_liquidated' => count($paymentIds),
                    'refunds_count' => $refunds->count(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function getCurrentCashBalance($date)
    {
        // Obtener el balance actual de caja con lock pesimista
        $lastMovement = CashMovement::whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->lockForUpdate()
            ->first();

        if (! $lastMovement) {
            // Si no hay movimientos hoy, buscar el último balance con lock
            $lastMovement = CashMovement::where('created_at', '<', $date->startOfDay())
                ->orderBy('created_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();
        }

        return $lastMovement ? $lastMovement->balance_after : 0;
    }
}
