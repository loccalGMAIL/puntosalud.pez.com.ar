<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Re-numera todos los recibos existentes con un secuencial global de 6 dígitos.
     *
     * Los recibos se ordenan por payment_date ASC, id ASC y se les asigna
     * 000001, 000002, ... sin reinicio mensual ni anual.
     */
    public function up(): void
    {
        $payments = DB::table('payments')
            ->whereNotNull('receipt_number')
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id');

        $counter = 1;
        foreach ($payments as $paymentId) {
            DB::table('payments')
                ->where('id', $paymentId)
                ->update(['receipt_number' => str_pad($counter, 6, '0', STR_PAD_LEFT)]);
            $counter++;
        }
    }

    /**
     * Esta operación no es reversible sin haber guardado los números originales.
     */
    public function down(): void
    {
        throw new \Exception('Esta migración no es reversible. Los números de recibo originales no fueron guardados.');
    }
};
