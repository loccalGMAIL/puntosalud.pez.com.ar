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
        Schema::create('liquidation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('liquidation_id');
            $table->unsignedBigInteger('payment_appointment_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->string('concept')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('liquidation_id')
                  ->references('id')
                  ->on('professional_liquidations')
                  ->onDelete('cascade');
            $table->foreign('payment_appointment_id')
                  ->references('id')
                  ->on('payment_appointments');
            $table->foreign('payment_id')
                  ->references('id')
                  ->on('payments');
            $table->foreign('appointment_id')
                  ->references('id')
                  ->on('appointments');
            
            // Índices
            $table->index('liquidation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidation_details');
    }
};