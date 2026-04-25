<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipt_sequences', function (Blueprint $table) {
            $table->string('key', 50)->primary();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
        });

        // Inicializar el contador tomando el maximo recibo existente.
        $maxExisting = null;
        if (Schema::hasTable('payments')) {
            $maxExisting = DB::table('payments')
                ->whereNotNull('receipt_number')
                ->max('receipt_number');
        }

        $maxExistingNumber = $maxExisting ? (int) $maxExisting : 0;

        DB::table('receipt_sequences')->insert([
            'key' => 'payments_receipt',
            // Guardamos el siguiente numero a asignar
            'next_number' => $maxExistingNumber + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_sequences');
    }
};
