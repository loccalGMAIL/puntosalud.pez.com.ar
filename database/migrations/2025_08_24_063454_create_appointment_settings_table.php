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
        Schema::create('appointment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professional_id');
            $table->integer('default_duration_minutes')->default(30);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('professional_id')
                  ->references('id')
                  ->on('professionals')
                  ->onDelete('cascade');
            
            // Unique constraint
            $table->unique('professional_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_settings');
    }
};