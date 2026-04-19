<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE whatsapp_messages MODIFY type ENUM('reminder', 'creation', 'cancellation') NOT NULL DEFAULT 'reminder'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE whatsapp_messages MODIFY type ENUM('reminder', 'creation') NOT NULL DEFAULT 'reminder'");
    }
};
