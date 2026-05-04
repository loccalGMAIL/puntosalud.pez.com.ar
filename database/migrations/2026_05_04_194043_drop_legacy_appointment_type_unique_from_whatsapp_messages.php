<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El índice legacy se llama literalmente `whatsapp_messages_appointment_type_unique`
        // (sin `_id_`). La migración del 2026-04-28 intentó dropearlo con
        // `dropUnique(['appointment_id','type'])`, pero Laravel auto-genera
        // `whatsapp_messages_appointment_id_type_unique`, que no matchea — y por eso
        // el unique sobrevivió y rompía los retries de creation/cancellation.
        $exists = collect(DB::select('SHOW INDEX FROM whatsapp_messages'))
            ->contains(fn ($i) => $i->Key_name === 'whatsapp_messages_appointment_type_unique');

        if ($exists) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->dropIndex('whatsapp_messages_appointment_type_unique');
            });
        }
    }

    public function down(): void
    {
        // No-op: el índice era legacy y bloqueaba los reintentos. No se restaura.
    }
};
