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
        // Modificar el ENUM para agregar 'cancelled'
        DB::statement("ALTER TABLE payments MODIFY COLUMN liquidation_status ENUM('pending', 'liquidated', 'not_applicable', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volver al ENUM original (sin 'cancelled')
        // NOTA: Si hay registros con 'cancelled', primero cambiarlos a 'not_applicable'
        DB::statement("UPDATE payments SET liquidation_status = 'not_applicable' WHERE liquidation_status = 'cancelled'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN liquidation_status ENUM('pending', 'liquidated', 'not_applicable') NOT NULL DEFAULT 'pending'");
    }
};
