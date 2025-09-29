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
        Schema::create('professional_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professional_id');
            $table->tinyInteger('day_of_week')->comment('1=Lunes, 7=Domingo');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedBigInteger('office_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('professional_id')
                ->references('id')
                ->on('professionals')
                ->onDelete('cascade');
            $table->foreign('office_id')
                ->references('id')
                ->on('offices');

            // Índices
            $table->index('professional_id');
            $table->index('day_of_week');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_schedules');
    }
};
