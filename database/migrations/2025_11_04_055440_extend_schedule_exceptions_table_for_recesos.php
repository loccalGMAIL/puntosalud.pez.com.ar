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
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            // Agregar tipo de excepción
            $table->enum('type', ['holiday', 'vacation', 'custom'])->default('custom')->after('reason');

            // Para vacaciones de profesionales específicos
            $table->foreignId('professional_id')->nullable()->after('type')->constrained()->onDelete('cascade');

            // Fecha final para rangos (vacaciones)
            $table->date('end_date')->nullable()->after('exception_date');

            // Estado activo/inactivo
            $table->boolean('is_active')->default(true)->after('professional_id');

            // Índices
            $table->index(['type', 'exception_date']);
            $table->index(['professional_id', 'exception_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            $table->dropForeign(['professional_id']);
            $table->dropIndex(['type', 'exception_date']);
            $table->dropIndex(['professional_id', 'exception_date']);
            $table->dropColumn(['type', 'professional_id', 'end_date', 'is_active']);
        });
    }
};
