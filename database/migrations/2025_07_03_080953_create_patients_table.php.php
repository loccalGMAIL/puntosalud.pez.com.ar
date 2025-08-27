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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('dni', 20)->unique()->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('health_insurance', 100)->nullable();
            $table->string('health_insurance_number', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('dni');
            $table->index(['last_name', 'first_name']);
            $table->index('health_insurance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};