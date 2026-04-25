<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\MovementType;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashMovementService
{
    private ?bool $hasPaymentMethodColumn = null;

    public function createForPayment(Payment $payment, ?string $fallbackBaseDescription = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($payment, $fallbackBaseDescription, $userId) {
            if (! CashMovement::isCashOpenToday()) {
                throw new \RuntimeException('No se pueden registrar pagos. La caja debe estar abierta para realizar esta operación.');
            }

            $paymentDetails = $payment->paymentDetails()
                ->where('received_by', 'centro')
                ->get();

            if ($paymentDetails->isEmpty()) {
                return;
            }

            $movementTypeCode = $payment->payment_type === 'refund' ? 'refund' : 'patient_payment';
            $baseDescription = $payment->concept ?: $fallbackBaseDescription ?: $this->defaultDescriptionForPayment($payment);

            $currentBalance = CashMovement::getCurrentBalanceWithLock();

            foreach ($paymentDetails as $paymentDetail) {
                $amount = $payment->payment_type === 'refund' ? -$paymentDetail->amount : $paymentDetail->amount;
                $currentBalance += $amount;

                $data = [
                    'movement_type_id' => MovementType::getIdByCode($movementTypeCode),
                    'amount' => $amount,
                    'description' => $baseDescription . ' - ' . $this->getPaymentMethodLabel($paymentDetail->payment_method),
                    'reference_type' => Payment::class,
                    'reference_id' => $payment->id,
                    'balance_after' => $currentBalance,
                    'user_id' => $userId ?? Auth::id(),
                ];

                if ($this->cashMovementsHasPaymentMethodColumn()) {
                    $data['payment_method'] = $paymentDetail->payment_method;
                }

                CashMovement::create($data);
            }
        });
    }

    public function reverseForPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            CashMovement::query()
                ->where('reference_type', Payment::class)
                ->where('reference_id', $payment->id)
                ->delete();

            $this->recalculateBalances();
        });
    }

    public function recalculateBalances(): void
    {
        $balanceCents = 0;

        CashMovement::withoutEvents(function () use (&$balanceCents) {
            CashMovement::query()
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->chunk(500, function ($movements) use (&$balanceCents) {
                    foreach ($movements as $movement) {
                        $balanceCents += $this->toCents($movement->amount);
                        $movement->forceFill(['balance_after' => $this->fromCents($balanceCents)])->save();
                    }
                });
        });
    }

    private function getPaymentMethodLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cash' => 'Efectivo',
            'transfer' => 'Transferencia',
            'debit_card' => 'Débito',
            'credit_card' => 'Crédito',
            'qr' => 'QR',
            default => ucfirst($paymentMethod),
        };
    }

    private function defaultDescriptionForPayment(Payment $payment): string
    {
        $patientName = $payment->patient?->full_name;

        $label = match ($payment->payment_type) {
            'single' => 'Pago individual',
            'package_purchase' => 'Paquete de sesiones',
            'refund' => 'Reembolso',
            'manual_income' => 'Ingreso manual',
            default => 'Pago',
        };

        return $patientName ? $label . ' - ' . $patientName : $label;
    }

    private function cashMovementsHasPaymentMethodColumn(): bool
    {
        if ($this->hasPaymentMethodColumn !== null) {
            return $this->hasPaymentMethodColumn;
        }

        $this->hasPaymentMethodColumn = Schema::hasColumn('cash_movements', 'payment_method');

        return $this->hasPaymentMethodColumn;
    }

    private function toCents(string|int|float $amount): int
    {
        $str = trim((string) $amount);
        if ($str === '') {
            return 0;
        }

        $negative = false;
        if (str_starts_with($str, '-')) {
            $negative = true;
            $str = substr($str, 1);
        }

        $str = str_replace(',', '.', $str);
        [$whole, $dec] = array_pad(explode('.', $str, 2), 2, '');

        $whole = preg_replace('/\\D/', '', $whole) ?: '0';
        $dec = preg_replace('/\\D/', '', $dec);
        $dec = substr(str_pad($dec, 2, '0'), 0, 2);

        $cents = ((int) $whole) * 100 + (int) $dec;

        return $negative ? -$cents : $cents;
    }

    private function fromCents(int $cents): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);

        $whole = intdiv($cents, 100);
        $dec = $cents % 100;

        $value = $whole . '.' . str_pad((string) $dec, 2, '0', STR_PAD_LEFT);

        return $negative ? '-' . $value : $value;
    }
}
