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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->dateTime('payment_date');
            $table->enum('payment_type', ['single', 'package', 'refund'])->default('single');
            $table->enum('payment_method', ['cash', 'transfer', 'card'])->default('cash');
            $table->decimal('amount', 10, 2)->comment('Negativo para devoluciones');
            $table->integer('sessions_included')->default(1)->comment('Cantidad de sesiones incluidas');
            $table->integer('sessions_used')->default(0)->comment('Sesiones utilizadas');
            $table->enum('liquidation_status', ['pending', 'liquidated', 'not_applicable'])->default('pending');
            $table->dateTime('liquidated_at')->nullable();
            $table->string('concept')->nullable();
            $table->string('receipt_number', 50)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            // Índices
            $table->index('patient_id');
            $table->index('payment_date');
            $table->index('liquidation_status');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
