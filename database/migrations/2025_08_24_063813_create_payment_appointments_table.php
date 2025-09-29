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
        Schema::create('payment_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('appointment_id');
            $table->decimal('allocated_amount', 10, 2);
            $table->boolean('is_liquidation_trigger')->default(false)
                ->comment('TRUE para primera sesión de paquete');
            $table->timestamps();

            // Foreign keys
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->onDelete('cascade');
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments');

            // Unique constraint
            $table->unique(['payment_id', 'appointment_id']);

            // Índices
            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_appointments');
    }
};
