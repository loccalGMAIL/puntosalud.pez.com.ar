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
        Schema::table('payment_details', function (Blueprint $table) {
            $table->foreignId('liquidation_id')->nullable()
                ->after('received_by')
                ->constrained('professional_liquidations')
                ->nullOnDelete()
                ->comment('Liquidación en la que se incluyó este payment_detail');

            $table->timestamp('liquidated_at')->nullable()
                ->after('liquidation_id')
                ->comment('Fecha y hora en que se liquidó este payment_detail');

            // Índice para optimizar queries de payment_details no liquidados
            $table->index('liquidation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropForeign(['liquidation_id']);
            $table->dropIndex(['liquidation_id']);
            $table->dropColumn(['liquidation_id', 'liquidated_at']);
        });
    }
};
