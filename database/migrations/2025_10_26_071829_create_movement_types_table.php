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
        Schema::create('movement_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Código único del tipo de movimiento');
            $table->string('name', 100)->comment('Nombre descriptivo del tipo');
            $table->text('description')->nullable()->comment('Descripción detallada del tipo');
            $table->string('category', 50)->comment('Categoría: main_type, expense_detail, income_detail, withdrawal_detail');
            $table->tinyInteger('affects_balance')->default(0)->comment('1: ingreso, -1: egreso, 0: neutral');
            $table->string('icon', 10)->nullable()->comment('Emoji o icono para UI');
            $table->string('color', 20)->nullable()->comment('Color para UI (green, red, blue, etc.)');
            $table->boolean('is_active')->default(true)->comment('Si el tipo está activo');
            $table->foreignId('parent_type_id')->nullable()->constrained('movement_types')->onDelete('cascade')->comment('ID del tipo padre para jerarquía');
            $table->integer('order')->default(0)->comment('Orden de visualización');
            $table->timestamps();

            // Índices para mejorar performance
            $table->index('code');
            $table->index('category');
            $table->index('parent_type_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_types');
    }
};
