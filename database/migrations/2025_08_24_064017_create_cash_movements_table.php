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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('movement_date');
            $table->enum('type', [
                'patient_payment', 
                'professional_payment', 
                'expense', 
                'refund', 
                'other'
            ]);
            $table->decimal('amount', 10, 2)->comment('Positivo=ingreso, Negativo=egreso');
            $table->text('description')->nullable();
            $table->string('reference_type', 50)->nullable()->comment('payment, liquidation, etc');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 10, 2)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
            
            // Índices
            $table->index('movement_date');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};