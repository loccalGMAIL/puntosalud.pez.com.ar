<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('professionals', 'birthday')) {
            Schema::table('professionals', function (Blueprint $table) {
                $table->date('birthday')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('professionals', 'birthday')) {
            Schema::table('professionals', function (Blueprint $table) {
                $table->dropColumn('birthday');
            });
        }
    }
};
