<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            $table->foreignId('movement_type_id')->constrained('movement_types');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 30)->nullable();
            $table->string('description', 500);
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['expense_date', 'movement_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
