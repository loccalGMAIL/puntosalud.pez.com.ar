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
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'transfer', 'debit_card', 'credit_card', 'other']);
            $table->decimal('amount', 10, 2);
            $table->enum('received_by', ['centro', 'profesional'])->default('centro');
            $table->string('reference')->nullable()->comment('Número de comprobante, referencia de transferencia, etc.');
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('payment_id');
            $table->index('payment_method');
            $table->index('received_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};
