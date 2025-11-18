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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sessions_included')->comment('Número de sesiones que incluye el paquete');
            $table->decimal('price', 10, 2)->comment('Precio del paquete');
            $table->boolean('is_active')->default(true)->comment('Si el paquete está disponible para venta');
            $table->timestamps();

            // Índices
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
