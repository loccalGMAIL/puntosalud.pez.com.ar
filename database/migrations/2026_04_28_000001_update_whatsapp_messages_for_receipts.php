<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            // El unique compuesto (appointment_id, type) respalda el FK de appointment_id.
            // Antes de dropearlo, crear un índice simple sobre appointment_id para que el FK no quede huérfano.
            $table->index('appointment_id', 'whatsapp_messages_appointment_id_index');
            $table->dropUnique(['appointment_id', 'type']);
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN type ENUM('reminder','creation','cancellation','receipt') NOT NULL DEFAULT 'reminder'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN type ENUM('reminder','creation','cancellation') NOT NULL DEFAULT 'reminder'");
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->unique(['appointment_id', 'type']);
            $table->dropIndex('whatsapp_messages_appointment_id_index');
        });
    }
};
