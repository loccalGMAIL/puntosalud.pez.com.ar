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
        Schema::create('professional_liquidations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professional_id');
            $table->date('liquidation_date');
            $table->enum('sheet_type', ['arrival', 'liquidation'])->default('liquidation');
            $table->integer('appointments_total')->default(0);
            $table->integer('appointments_attended')->default(0);
            $table->integer('appointments_absent')->default(0);
            $table->decimal('total_collected', 10, 2)->default(0);
            $table->decimal('professional_commission', 10, 2)->default(0);
            $table->decimal('clinic_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->dateTime('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('professional_id')
                ->references('id')
                ->on('professionals');
            $table->foreign('paid_by')
                ->references('id')
                ->on('users');

            // Índices
            $table->index(['professional_id', 'liquidation_date']);
            $table->index('payment_status');
            $table->index('liquidation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_liquidations');
    }
};
