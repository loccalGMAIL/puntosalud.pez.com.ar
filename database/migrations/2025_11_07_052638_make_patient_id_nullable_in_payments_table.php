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
        Schema::table('payments', function (Blueprint $table) {
            // Hacer patient_id nullable para soportar ingresos manuales
            $table->foreignId('patient_id')->nullable()->change();

            // Agregar campo para almacenar el tipo de ingreso manual (si aplica)
            $table->string('income_category')->nullable()->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revertir patient_id a no nullable
            $table->foreignId('patient_id')->nullable(false)->change();

            // Eliminar campo income_category
            $table->dropColumn('income_category');
        });
    }
};
