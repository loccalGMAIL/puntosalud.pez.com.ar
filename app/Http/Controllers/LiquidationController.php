<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professional;
use App\Models\CashMovement;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiquidationController extends Controller
{
    public function processLiquidation(Request $request)
    {
        $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $professional = Professional::findOrFail($request->professional_id);
            $amount = $request->amount;
            $date = Carbon::parse($request->date);

            // Verificar que la caja esté abierta
            $cashStatus = CashMovement::getCashStatusForDate($date);
            if (!$cashStatus['is_open']) {
                throw new \Exception('La caja debe estar abierta para procesar liquidaciones.');
            }

            // Verificar turnos pendientes del profesional
            $pendingAppointments = Appointment::where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->where('status', 'scheduled')
                ->count();

            if ($pendingAppointments > 0) {
                throw new \Exception("No se puede liquidar. El profesional tiene {$pendingAppointments} " .
                                   ($pendingAppointments === 1 ? 'turno pendiente' : 'turnos pendientes') .
                                   " sin atender del día {$date->format('d/m/Y')}.");
            }

            // Verificar turnos atendidos sin cobrar
            $unpaidAppointments = Appointment::where('professional_id', $professional->id)
                ->whereDate('appointment_date', $date)
                ->where('status', 'attended')
                ->whereDoesntHave('paymentAppointments')
                ->count();

            if ($unpaidAppointments > 0) {
                throw new \Exception("No se puede liquidar. El profesional tiene {$unpaidAppointments} " .
                                   ($unpaidAppointments === 1 ? 'turno atendido' : 'turnos atendidos') .
                                   " sin cobrar del día {$date->format('d/m/Y')}.");
            }

            // Verificar que hay suficiente efectivo en caja
            $currentBalance = $this->getCurrentCashBalance($date);
            if ($currentBalance < $amount) {
                throw new \Exception("Saldo insuficiente en caja. Disponible: $" . number_format($currentBalance, 2));
            }

            // Crear movimiento de caja por pago al profesional
            CashMovement::create([
                'movement_date' => $date,
                'type' => 'professional_payment',
                'amount' => -$amount, // Negativo porque es una salida de dinero
                'description' => "Liquidación profesional: {$professional->name}",
                'reference_type' => Professional::class,
                'reference_id' => $professional->id,
                'balance_after' => $currentBalance - $amount,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Liquidación procesada correctamente',
                'data' => [
                    'professional_name' => $professional->name,
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'new_balance' => $currentBalance - $amount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function getCurrentCashBalance($date)
    {
        // Obtener el balance actual de caja para la fecha
        $lastMovement = CashMovement::whereDate('movement_date', $date)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastMovement) {
            // Si no hay movimientos hoy, buscar el último balance
            $lastMovement = CashMovement::where('movement_date', '<', $date->startOfDay())
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return $lastMovement ? $lastMovement->balance_after : 0;
    }
}
