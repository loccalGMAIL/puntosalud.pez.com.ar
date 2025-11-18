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

            // TEMPORAL: Excepción para Dra. Zalazar (ID=1) - Cobra directamente, no retira de caja
            $isDraZalazar = $professional->id === 1;

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

            // CAMBIO v2.6.0: Calcular comisión solo sobre payment_details recibidos por el centro y no liquidados
            // Obtener IDs de turnos atendidos
            $attendedAppointmentIds = $attendedAppointments->pluck('id');

            // Obtener payment_details del centro pendientes de liquidación
            $centroPaymentDetails = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments', function($q) use ($attendedAppointmentIds) {
                    $q->whereIn('appointment_id', $attendedAppointmentIds);
                })
                ->where('received_by', 'centro')
                ->whereNull('liquidation_id') // Solo no liquidados
                ->with(['payment.paymentAppointments' => function($q) use ($attendedAppointmentIds) {
                    $q->whereIn('appointment_id', $attendedAppointmentIds);
                }])
                ->get();

            // Calcular total solo de lo recibido por el centro
            $totalCollected = $centroPaymentDetails->sum('amount');
            $professionalCommission = $professional->calculateCommission($totalCollected);
            $clinicAmount = $totalCollected - $professionalCommission;

            // NUEVO v2.6.0: Obtener payment_details recibidos DIRECTAMENTE por el profesional
            $professionalPaymentDetails = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments', function($q) use ($attendedAppointmentIds) {
                    $q->whereIn('appointment_id', $attendedAppointmentIds);
                })
                ->where('received_by', 'profesional')
                ->whereNull('liquidation_id') // Solo no liquidados
                ->with(['payment.paymentAppointments' => function($q) use ($attendedAppointmentIds) {
                    $q->whereIn('appointment_id', $attendedAppointmentIds);
                }])
                ->get();

            // Total de pagos directos al profesional (transferencias que recibió directamente)
            $directPaymentsTotal = $professionalPaymentDetails->sum('amount');

            // Calcular la parte del centro sobre esos pagos directos
            // Si el profesional recibió $2000, debe pagar al centro (100% - commission_percentage)
            $clinicPercentage = 100 - $professional->commission_percentage;
            $clinicAmountFromDirect = $directPaymentsTotal * ($clinicPercentage / 100);

            // Obtener reintegros del día para este profesional (usando referencias polimórficas)
            $refunds = CashMovement::byType('expense')
                ->where('reference_type', 'App\Models\Professional')
                ->where('reference_id', $professional->id)
                ->whereDate('created_at', $date)
                ->get();

            $totalRefunds = $refunds->sum(function($refund) {
                return abs($refund->amount); // Los gastos son negativos, convertir a positivo
            });

            // NUEVO CÁLCULO: Monto neto considerando pagos directos
            // = (Comisión sobre pagos al centro) - (Parte del centro sobre pagos directos) - (Reintegros)
            $netProfessionalAmount = $professionalCommission - $clinicAmountFromDirect - $totalRefunds;

            // Para compatibilidad, mantenemos el cálculo anterior también
            $finalProfessionalAmount = $netProfessionalAmount;

            // Verificar que el monto ingresado coincida con el monto neto calculado
            if (abs($amount - $finalProfessionalAmount) > 0.01) {
                $message = "El monto ingresado (\${$amount}) no coincide con el monto neto calculado (\${$finalProfessionalAmount}).\n\n";
                $message .= "Detalle del cálculo:\n";
                $message .= "+ Comisión sobre pagos al centro: \${$professionalCommission} (sobre \${$totalCollected})\n";
                if ($directPaymentsTotal > 0) {
                    $message .= "- Parte del centro sobre pagos directos: \${$clinicAmountFromDirect} (sobre \${$directPaymentsTotal})\n";
                }
                if ($totalRefunds > 0) {
                    $message .= "- Reintegros: \${$totalRefunds}\n";
                }
                $message .= "= Monto neto: \${$finalProfessionalAmount}";
                throw new \Exception($message);
            }

            // 8. Verificar que hay suficiente efectivo en caja (excepto Dra. Zalazar)
            if (!$isDraZalazar) {
                $currentBalance = $this->getCurrentCashBalance($date);
                if ($currentBalance < $amount) {
                    throw new \Exception('Saldo insuficiente en caja. Disponible: $'.number_format($currentBalance, 2));
                }
            } else {
                $currentBalance = $this->getCurrentCashBalance($date);
            }

            // 9. Crear registro en professional_liquidations
            $notes = "Liquidación procesada el {$date->format('d/m/Y')}";
            if ($directPaymentsTotal > 0) {
                $notes .= " - Pagos directos al profesional: \${$directPaymentsTotal}";
            }
            if ($totalRefunds > 0) {
                $notes .= " - Reintegros descontados: \${$totalRefunds}";
            }

            $liquidation = ProfessionalLiquidation::create([
                'professional_id' => $professional->id,
                'liquidation_date' => $date,
                'sheet_type' => 'liquidation',
                'appointments_total' => $totalAppointments,
                'appointments_attended' => $attendedAppointments->count(),
                'appointments_absent' => $absentAppointments,
                'total_collected' => $totalCollected,
                'direct_payments_total' => $directPaymentsTotal,
                'professional_commission' => $professionalCommission,
                'clinic_amount' => $clinicAmount,
                'clinic_amount_from_direct' => $clinicAmountFromDirect,
                'net_professional_amount' => $netProfessionalAmount,
                'payment_status' => 'paid', // Se marca como pagado inmediatamente
                'payment_method' => 'cash',
                'paid_at' => now(),
                'paid_by' => auth()->id(),
                'notes' => "Liquidación procesada el {$date->format('d/m/Y')}".
                          ($totalRefunds > 0 ? " - Reintegros descontados: \${$totalRefunds}" : "").
                          ($isDraZalazar ? " - PAGO DIRECTO: Profesional cobra directamente, no retira de caja" : ""),
            ]);

            // 10. Crear detalles en liquidation_details
            $paymentIds = [];

            // 10.1 Procesar payment_details del centro (comisión al profesional)
            foreach ($centroPaymentDetails as $paymentDetail) {
                // Obtener el payment_appointment y appointment relacionado
                $paymentAppointment = $paymentDetail->payment->paymentAppointments
                    ->whereIn('appointment_id', $attendedAppointmentIds)
                    ->first();

                if (!$paymentAppointment) {
                    continue; // Saltar si no hay relación (no debería pasar)
                }

                $appointment = $paymentAppointment->appointment;
                $paymentDetailAmount = $paymentDetail->amount;
                $paymentDetailCommission = $professional->calculateCommission($paymentDetailAmount);

                // Crear detalle de liquidación vinculado al payment_detail específico
                LiquidationDetail::create([
                    'liquidation_id' => $liquidation->id,
                    'payment_detail_id' => $paymentDetail->id,
                    'payment_appointment_id' => $paymentAppointment->id,
                    'payment_id' => $paymentDetail->payment_id,
                    'appointment_id' => $appointment->id,
                    'amount' => $paymentDetailAmount,
                    'commission_amount' => $paymentDetailCommission,
                    'concept' => "Turno {$appointment->appointment_date->format('H:i')} - {$appointment->patient->full_name} - {$this->getPaymentMethodLabel($paymentDetail->payment_method)} (Centro)",
                ]);

                // Marcar payment_detail como liquidado
                $paymentDetail->update([
                    'liquidation_id' => $liquidation->id,
                    'liquidated_at' => now(),
                ]);

                $paymentIds[] = $paymentDetail->payment_id;
            }

            // 10.2 Procesar payment_details directos al profesional (parte del centro)
            foreach ($professionalPaymentDetails as $paymentDetail) {
                // Obtener el payment_appointment y appointment relacionado
                $paymentAppointment = $paymentDetail->payment->paymentAppointments
                    ->whereIn('appointment_id', $attendedAppointmentIds)
                    ->first();

                if (!$paymentAppointment) {
                    continue; // Saltar si no hay relación (no debería pasar)
                }

                $appointment = $paymentAppointment->appointment;
                $paymentDetailAmount = $paymentDetail->amount;
                // Para pagos directos, calculamos la parte del centro (NO la comisión del profesional)
                $clinicPart = $paymentDetailAmount * ($clinicPercentage / 100);

                // Crear detalle de liquidación - nota: commission_amount es NEGATIVO porque el profesional debe pagarle al centro
                LiquidationDetail::create([
                    'liquidation_id' => $liquidation->id,
                    'payment_detail_id' => $paymentDetail->id,
                    'payment_appointment_id' => $paymentAppointment->id,
                    'payment_id' => $paymentDetail->payment_id,
                    'appointment_id' => $appointment->id,
                    'amount' => $paymentDetailAmount,
                    'commission_amount' => -$clinicPart, // Negativo: el profesional debe al centro
                    'concept' => "Turno {$appointment->appointment_date->format('H:i')} - {$appointment->patient->full_name} - {$this->getPaymentMethodLabel($paymentDetail->payment_method)} (Profesional directo - parte centro)",
                ]);

                // Marcar payment_detail como liquidado
                $paymentDetail->update([
                    'liquidation_id' => $liquidation->id,
                    'liquidated_at' => now(),
                ]);

                $paymentIds[] = $paymentDetail->payment_id;
            }

            // 11. Actualizar liquidation_status en payments (solo si todos sus payment_details están liquidados)
            if (!empty($paymentIds)) {
                $uniquePaymentIds = array_unique($paymentIds);
                foreach ($uniquePaymentIds as $paymentId) {
                    $payment = \App\Models\Payment::find($paymentId);
                    // Verificar si todos los payment_details del pago están liquidados
                    $pendingDetails = $payment->paymentDetails()->whereNull('liquidation_id')->count();
                    if ($pendingDetails === 0) {
                        $payment->update([
                            'liquidation_status' => 'liquidated',
                            'liquidated_at' => now(),
                        ]);
                    }
                }
            }

            // 12. Crear movimiento de caja por pago al profesional (excepto Dra. Zalazar)
            if (!$isDraZalazar) {
                CashMovement::create([
                    'movement_type_id' => MovementType::getIdByCode('professional_payment'),
                    'amount' => -$amount, // Negativo porque es una salida de dinero
                    'description' => "Liquidación profesional: {$professional->full_name} - {$attendedAppointments->count()} turnos",
                    'reference_type' => ProfessionalLiquidation::class,
                    'reference_id' => $liquidation->id,
                    'balance_after' => $currentBalance - $amount,
                    'user_id' => auth()->id(),
                ]);
            }

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

    /**
     * Obtener etiqueta legible del método de pago
     */
    private function getPaymentMethodLabel(string $paymentMethod): string
    {
        return match($paymentMethod) {
            'cash' => 'Efectivo',
            'transfer' => 'Transferencia',
            'debit_card' => 'Débito',
            'credit_card' => 'Crédito',
            'other' => 'Otro',
            default => ucfirst($paymentMethod)
        };
    }
}
