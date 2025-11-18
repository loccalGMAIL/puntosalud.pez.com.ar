<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la tabla payments ya existe (indica que hay datos pre-v2.6.0)
        $hasOldPaymentsTable = Schema::hasTable('payments');

        if ($hasOldPaymentsTable) {
            // PASO 0: Eliminar foreign keys de la tabla payments original para evitar conflictos
            // Intentamos eliminar los FKs comunes, pero no fallar si no existen
            try {
                DB::statement('ALTER TABLE payments DROP FOREIGN KEY IF EXISTS payments_patient_id_foreign');
                DB::statement('ALTER TABLE payments DROP FOREIGN KEY IF EXISTS payments_created_by_foreign');
            } catch (\Exception $e) {
                // Si no existen los FKs, continuamos
            }

            // PASO 1: Renombrar tabla payments a payments_old (solo si existe)
            Schema::rename('payments', 'payments_old');
        }

        // PASO 2: Crear nueva tabla payments con estructura actualizada
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();

            // Tipo de pago
            $table->enum('payment_type', ['single', 'package_purchase', 'refund', 'manual_income'])
                ->comment('single: pago individual, package_purchase: compra de paquete, refund: reembolso, manual_income: ingreso manual');

            // Información del pago
            $table->dateTime('payment_date');
            $table->string('receipt_number', 50)->unique();
            $table->decimal('total_amount', 10, 2)->comment('Monto total del pago (suma de payment_details)');
            $table->boolean('is_advance_payment')->default(false)->comment('Si es un pago anticipado sin turno asignado');
            $table->string('concept')->nullable()->comment('Concepto del pago o descripción');

            // Estado
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed')
                ->comment('Estado del pago: pending (pendiente), confirmed (confirmado), cancelled (anulado)');

            // Liquidación
            $table->enum('liquidation_status', ['pending', 'liquidated', 'cancelled', 'not_applicable'])->default('pending')
                ->comment('Estado de liquidación profesional');
            $table->dateTime('liquidated_at')->nullable();

            // Para ingresos manuales
            $table->string('income_category')->nullable()->comment('Categoría del ingreso manual (código de movement_type)');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Índices
            $table->index('patient_id');
            $table->index('payment_type');
            $table->index('payment_date');
            $table->index('receipt_number');
            $table->index('liquidation_status');
            $table->index('status');
            $table->index('is_advance_payment');
        });

        // PASO 3: Crear tabla temporal para mapeo de IDs (solo si hay datos antiguos)
        if ($hasOldPaymentsTable) {
            DB::statement('
                CREATE TABLE payment_id_mapping (
                    old_payment_id BIGINT UNSIGNED PRIMARY KEY,
                    new_payment_id BIGINT UNSIGNED,
                    INDEX (new_payment_id)
                )
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar tabla de mapeo
        Schema::dropIfExists('payment_id_mapping');

        // Eliminar nueva tabla payments
        Schema::dropIfExists('payments');

        // Restaurar nombre original
        Schema::rename('payments_old', 'payments');
    }
};
