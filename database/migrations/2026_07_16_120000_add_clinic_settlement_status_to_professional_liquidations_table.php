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
            // Estado de la entrega de la parte del centro cuando el neto es negativo
            // (el profesional debe entregar al centro). Las liquidaciones con neto < 0
            // nacen 'pending' y pasan a 'settled' al registrarse el ingreso de la entrega.
            // Las filas existentes quedan 'not_required' para no bloquear cajas ya abiertas.
            $table->enum('clinic_settlement_status', ['not_required', 'pending', 'settled'])
                ->default('not_required')
                ->after('net_professional_amount');

            $table->index('clinic_settlement_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_liquidations', function (Blueprint $table) {
            $table->dropIndex(['clinic_settlement_status']);
            $table->dropColumn('clinic_settlement_status');
        });
    }
};
