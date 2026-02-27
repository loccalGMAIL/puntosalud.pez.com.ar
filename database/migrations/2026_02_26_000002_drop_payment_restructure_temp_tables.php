<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina las tablas temporales creadas durante la reestructuración de pagos (v2.6.0).
     *
     * payments_old y payment_id_mapping son tablas auxiliares del proceso de migración de datos.
     * En instalaciones con migrate:fresh, payments existía vacía → la migración 100000 la renombraba
     * y nunca la eliminaba. Resultado: tablas vacías permanentes sin modelo ni referencias activas.
     */
    public function up(): void
    {
        Schema::dropIfExists('payments_old');
        Schema::dropIfExists('payment_id_mapping');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No restauramos las tablas temporales en el rollback; eran basura de la migración 100000.
    }
};
