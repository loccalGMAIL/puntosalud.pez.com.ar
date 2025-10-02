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
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('professional_id')->nullable()->after('user_id');

            $table->foreign('professional_id')
                ->references('id')
                ->on('professionals')
                ->onDelete('set null');

            $table->index('professional_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['professional_id']);
            $table->dropIndex(['professional_id']);
            $table->dropColumn('professional_id');
        });
    }
};
