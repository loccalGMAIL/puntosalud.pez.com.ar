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
        Schema::table('liquidation_details', function (Blueprint $table) {
            $table->foreignId('payment_detail_id')->nullable()
                ->after('liquidation_id')
                ->constrained('payment_details')
                ->nullOnDelete()
                ->comment('Payment detail específico incluido en esta liquidación');

            // Índice para optimizar queries
            $table->index('payment_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liquidation_details', function (Blueprint $table) {
            $table->dropForeign(['payment_detail_id']);
            $table->dropIndex(['payment_detail_id']);
            $table->dropColumn('payment_detail_id');
        });
    }
};
