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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professional_id');
            $table->unsignedBigInteger('patient_id');
            $table->dateTime('appointment_date');
            $table->integer('duration')->default(30)->comment('Duración en minutos');
            $table->unsignedBigInteger('office_id')->nullable();
            $table->enum('status', ['scheduled', 'attended', 'absent', 'cancelled'])->default('scheduled');
            $table->decimal('estimated_amount', 10, 2)->nullable()->comment('Monto estimado inicial');
            $table->decimal('final_amount', 10, 2)->nullable()->comment('Monto final después de atención');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('professional_id')
                  ->references('id')
                  ->on('professionals');
            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients');
            $table->foreign('office_id')
                  ->references('id')
                  ->on('offices');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users');
            
            // Índices
            $table->index(['professional_id', 'appointment_date']);
            $table->index('patient_id');
            $table->index('status');
            $table->index('appointment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};