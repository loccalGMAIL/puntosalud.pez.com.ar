<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_slot_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->date('block_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['professional_id', 'block_date', 'start_time'], 'slot_blocks_unique');
            $table->index(['block_date', 'professional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_slot_blocks');
    }
};
