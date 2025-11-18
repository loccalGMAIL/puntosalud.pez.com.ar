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
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer', 'debit_card', 'credit_card', 'other'])
                ->nullable()
                ->after('amount')
                ->comment('Método de pago del movimiento (solo aplica a pagos de pacientes)');

            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
            $table->dropColumn('payment_method');
        });
    }
};
