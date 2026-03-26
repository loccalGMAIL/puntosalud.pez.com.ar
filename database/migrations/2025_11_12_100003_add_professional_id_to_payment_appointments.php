<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_appointments', function (Blueprint $table) {
            $table->foreignId('professional_id')->nullable()
                ->after('appointment_id')
                ->constrained('professionals')
                ->nullOnDelete()
                ->comment('Profesional del turno (desnormalización para optimizar queries de liquidación)');

            // Índice para optimizar queries de liquidación por profesional
            $table->index(['professional_id', 'is_liquidation_trigger'], 'pa_prof_liq_trigger_idx');
        });

        // Poblar el campo professional_id con datos existentes (sintaxis compatible con SQLite y MySQL)
        DB::statement('
            UPDATE payment_appointments
            SET professional_id = (
                SELECT appointments.professional_id
                FROM appointments
                WHERE appointments.id = payment_appointments.appointment_id
            )
            WHERE appointment_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_appointments', function (Blueprint $table) {
            $table->dropForeign(['professional_id']);
            $table->dropIndex('pa_prof_liq_trigger_idx');
            $table->dropColumn('professional_id');
        });
    }
};
