<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->date('absence_date');
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(['professional_id', 'absence_date']);
            $table->index('absence_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_absences');
    }
};
