<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('professional_liquidations', function (Blueprint $table) {
            // Montos de pagos directos al profesional
            $table->decimal('direct_payments_total', 10, 2)->default(0)->after('total_collected')
                ->comment('Total de pagos recibidos directamente por el profesional (transferencias)');

            $table->decimal('clinic_amount_from_direct', 10, 2)->default(0)->after('clinic_amount')
                ->comment('Parte del centro sobre pagos directos del profesional');

            $table->decimal('net_professional_amount', 10, 2)->default(0)->after('professional_commission')
                ->comment('Monto neto: (comisión sobre pagos al centro) - (parte del centro sobre pagos directos)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_liquidations', function (Blueprint $table) {
            $table->dropColumn([
                'direct_payments_total',
                'clinic_amount_from_direct',
                'net_professional_amount'
            ]);
        });
    }
};
