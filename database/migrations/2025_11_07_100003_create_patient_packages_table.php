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
        Schema::create('patient_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete()->comment('Null si el paquete del catálogo fue eliminado');
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->integer('sessions_included')->comment('Sesiones incluidas en este paquete (snapshot al momento de compra)');
            $table->integer('sessions_used')->default(0)->comment('Sesiones ya utilizadas');
            $table->decimal('price_paid', 10, 2)->comment('Precio pagado por este paquete');
            $table->date('purchase_date');
            $table->date('expires_at')->nullable()->comment('Fecha de vencimiento del paquete');
            $table->enum('status', ['active', 'completed', 'expired', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('patient_id');
            $table->index('status');
            $table->index('purchase_date');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_packages');
    }
};
