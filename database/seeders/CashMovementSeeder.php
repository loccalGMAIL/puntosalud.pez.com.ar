<?php

namespace Database\Seeders;

use App\Models\CashMovement;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CashMovementSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        $movements = [];
        $runningBalance = 0;

        // Generar movimientos para los últimos 30 días
        $startDate = Carbon::today()->subDays(30);
        $endDate = Carbon::today();

        // Balance inicial
        $movements[] = [
            'movement_date' => $startDate->copy()->setTime(8, 0, 0),
            'type' => 'other',
            'amount' => 50000,
            'description' => 'Balance inicial de caja',
            'balance_after' => 50000,
            'user_id' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $runningBalance = 50000;

        // Crear movimientos basados en pagos existentes
        $payments = Payment::where('amount', '>', 0)->get();
        foreach ($payments as $payment) {
            $runningBalance += $payment->amount;
            $movements[] = [
                'movement_date' => $payment->payment_date,
                'type' => 'patient_payment',
                'amount' => $payment->amount,
                'description' => "Pago de paciente - {$payment->concept}",
                'reference_type' => 'Payment',
                'reference_id' => $payment->id,
                'balance_after' => $runningBalance,
                'user_id' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Crear algunos gastos aleatorios
        $expenses = [
            ['desc' => 'Pago de alquiler', 'amount' => -25000],
            ['desc' => 'Servicios públicos', 'amount' => -8500],
            ['desc' => 'Compra de suministros médicos', 'amount' => -12000],
            ['desc' => 'Mantenimiento de equipos', 'amount' => -5500],
            ['desc' => 'Material de oficina', 'amount' => -2800],
            ['desc' => 'Limpieza del consultorio', 'amount' => -3200],
            ['desc' => 'Internet y telefonía', 'amount' => -4500],
            ['desc' => 'Seguros', 'amount' => -7800],
            ['desc' => 'Combustible', 'amount' => -3000],
            ['desc' => 'Publicidad', 'amount' => -4200],
        ];

        foreach ($expenses as $expense) {
            $expenseDate = $startDate->copy()->addDays(rand(1, 25))->setTime(rand(10, 16), rand(0, 59));
            $runningBalance += $expense['amount'];
            
            $movements[] = [
                'movement_date' => $expenseDate,
                'type' => 'expense',
                'amount' => $expense['amount'],
                'description' => $expense['desc'],
                'balance_after' => $runningBalance,
                'user_id' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Crear algunos pagos a profesionales
        $professionalPayments = [
            ['desc' => 'Pago comisión Dr. Juan Pérez', 'amount' => -8500],
            ['desc' => 'Pago comisión Dra. María González', 'amount' => -12000],
            ['desc' => 'Pago comisión Dr. Carlos Martínez', 'amount' => -9200],
            ['desc' => 'Pago comisión Dra. Ana Rodríguez', 'amount' => -10800],
        ];

        foreach ($professionalPayments as $payment) {
            $paymentDate = $startDate->copy()->addDays(rand(5, 28))->setTime(rand(14, 17), rand(0, 59));
            $runningBalance += $payment['amount'];
            
            $movements[] = [
                'movement_date' => $paymentDate,
                'type' => 'professional_payment',
                'amount' => $payment['amount'],
                'description' => $payment['desc'],
                'balance_after' => $runningBalance,
                'user_id' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Incluir movimientos de reembolsos
        $refunds = Payment::where('amount', '<', 0)->get();
        foreach ($refunds as $refund) {
            $runningBalance += $refund->amount;
            $movements[] = [
                'movement_date' => $refund->payment_date,
                'type' => 'refund',
                'amount' => $refund->amount,
                'description' => "Reembolso a paciente - {$refund->concept}",
                'reference_type' => 'Payment',
                'reference_id' => $refund->id,
                'balance_after' => $runningBalance,
                'user_id' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Ordenar movimientos por fecha
        usort($movements, function ($a, $b) {
            return strcmp($a['movement_date'], $b['movement_date']);
        });

        // Recalcular balances en orden cronológico
        $balance = 0;
        foreach ($movements as &$movement) {
            $balance += $movement['amount'];
            $movement['balance_after'] = $balance;
        }

        // Insertar todos los movimientos
        foreach ($movements as $movement) {
            CashMovement::create($movement);
        }
    }
}