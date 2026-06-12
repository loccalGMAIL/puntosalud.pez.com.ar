<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            // Profesional cobra directamente: su liquidación no genera salida de caja
            $table->boolean('collects_directly')->default(false)->after('receives_transfers_directly');
        });

        // Preservar comportamiento existente: la Dra. Zalazar (ID=1) cobraba
        // directamente vía hardcode en LiquidationController
        DB::table('professionals')->where('id', 1)->update(['collects_directly' => true]);
    }

    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            $table->dropColumn('collects_directly');
        });
    }
};
