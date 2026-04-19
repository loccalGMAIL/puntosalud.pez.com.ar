<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar columna type si no existe aún
        if (! Schema::hasColumn('whatsapp_messages', 'type')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->enum('type', ['reminder', 'creation'])->default('reminder')->after('instance');
            });
        }

        // En MySQL no se puede eliminar un unique index mientras haya una FK que lo usa.
        // Hay que: soltar FK → soltar unique → recrear FK (usa índice normal) → agregar unique compuesto.
        DB::statement('ALTER TABLE whatsapp_messages DROP FOREIGN KEY whatsapp_messages_appointment_id_foreign');
        DB::statement('ALTER TABLE whatsapp_messages DROP INDEX whatsapp_messages_appointment_id_unique');
        DB::statement('ALTER TABLE whatsapp_messages ADD CONSTRAINT whatsapp_messages_appointment_id_foreign FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE whatsapp_messages ADD UNIQUE KEY whatsapp_messages_appointment_id_type_unique (appointment_id, type)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE whatsapp_messages DROP INDEX whatsapp_messages_appointment_id_type_unique');
        DB::statement('ALTER TABLE whatsapp_messages DROP FOREIGN KEY whatsapp_messages_appointment_id_foreign');
        DB::statement('ALTER TABLE whatsapp_messages ADD UNIQUE KEY whatsapp_messages_appointment_id_unique (appointment_id)');
        DB::statement('ALTER TABLE whatsapp_messages ADD CONSTRAINT whatsapp_messages_appointment_id_foreign FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE');

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
