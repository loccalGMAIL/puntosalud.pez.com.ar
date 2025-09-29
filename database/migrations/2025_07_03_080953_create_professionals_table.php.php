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
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->unsignedBigInteger('specialty_id')->nullable();
            $table->string('dni', 20)->unique()->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->decimal('commission_percentage', 5, 2)->default(70.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('specialty_id')->references('id')->on('specialties');

            // Índices
            $table->index('dni');
            $table->index('is_active');
            $table->index(['last_name', 'first_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
