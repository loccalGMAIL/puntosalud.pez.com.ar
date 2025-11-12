<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si existe la tabla payments_old (solo existe si ya había datos antes de v2.6.0)
        $hasOldData = DB::getSchemaBuilder()->hasTable('payments_old');

        if (!$hasOldData) {
            Log::info('=== MIGRACIÓN v2.6.0 SKIPPED: No hay datos antiguos que migrar (instalación limpia) ===');
            return;
        }

        // Laravel maneja las transacciones automáticamente en migraciones
        Log::info('=== INICIO MIGRACIÓN DE DATOS v2.6.0 ===');

        // PASO 1: Migrar datos de payments_old a payments
        $this->migratePayments();

        // PASO 2: Crear payment_details para cada pago
        $this->createPaymentDetails();

        // PASO 3: Crear patient_packages para pagos tipo 'package'
        $this->createPatientPackages();

        // PASO 4: Actualizar foreign keys en tablas relacionadas
        $this->updateRelatedTables();

        // PASO 5: Validar integridad de datos
        $this->validateMigration();

        Log::info('=== MIGRACIÓN COMPLETADA EXITOSAMENTE ===');
    }

    /**
     * Migra registros de payments_old a payments
     */
    private function migratePayments(): void
    {
        Log::info('Migrando payments_old → payments');

        $migratedCount = DB::statement("
            INSERT INTO payments (
                id,
                patient_id,
                payment_type,
                payment_date,
                receipt_number,
                total_amount,
                is_advance_payment,
                concept,
                status,
                liquidation_status,
                liquidated_at,
                income_category,
                created_by,
                created_at,
                updated_at
            )
            SELECT
                id,
                patient_id,
                payment_type,
                payment_date,
                receipt_number,
                amount as total_amount,
                0 as is_advance_payment,
                concept,
                'confirmed' as status,
                liquidation_status,
                liquidated_at,
                income_category,
                created_by,
                created_at,
                updated_at
            FROM payments_old
        ");

        // Guardar mapeo de IDs (en este caso son iguales porque usamos el mismo ID)
        DB::statement("
            INSERT INTO payment_id_mapping (old_payment_id, new_payment_id)
            SELECT id, id FROM payments_old
        ");

        $count = DB::table('payments')->count();
        Log::info("Migrados {$count} pagos");
    }

    /**
     * Crea payment_details para cada pago
     */
    private function createPaymentDetails(): void
    {
        Log::info('Creando payment_details desde payments_old');

        // Determinar received_by basado en el método de pago
        // - Transferencias a profesionales: 'profesional'
        // - Todo lo demás: 'centro'
        DB::statement("
            INSERT INTO payment_details (
                payment_id,
                payment_method,
                amount,
                received_by,
                reference,
                created_at,
                updated_at
            )
            SELECT
                id as payment_id,
                payment_method,
                amount,
                CASE
                    WHEN payment_method = 'transfer' AND patient_id IS NOT NULL THEN 'profesional'
                    ELSE 'centro'
                END as received_by,
                NULL as reference,
                created_at,
                updated_at
            FROM payments_old
        ");

        $count = DB::table('payment_details')->count();
        Log::info("Creados {$count} payment_details");
    }

    /**
     * Crea patient_packages para pagos de tipo 'package'
     */
    private function createPatientPackages(): void
    {
        Log::info('Creando patient_packages desde payments_old (tipo package)');

        // Solo crear patient_packages para pagos que tengan sessions_included > 1 o payment_type = 'package'
        DB::statement("
            INSERT INTO patient_packages (
                patient_id,
                package_id,
                payment_id,
                sessions_included,
                sessions_used,
                price_paid,
                purchase_date,
                expires_at,
                status,
                notes,
                created_at,
                updated_at
            )
            SELECT
                patient_id,
                NULL as package_id,
                id as payment_id,
                COALESCE(sessions_included, 1) as sessions_included,
                COALESCE(sessions_used, 0) as sessions_used,
                amount as price_paid,
                DATE(payment_date) as purchase_date,
                NULL as expires_at,
                CASE
                    WHEN COALESCE(sessions_used, 0) >= COALESCE(sessions_included, 1) THEN 'completed'
                    ELSE 'active'
                END as status,
                concept as notes,
                created_at,
                updated_at
            FROM payments_old
            WHERE payment_type = 'package'
               OR sessions_included > 1
        ");

        $count = DB::table('patient_packages')->count();
        Log::info("Creados {$count} patient_packages");
    }

    /**
     * Actualiza foreign keys en tablas relacionadas
     */
    private function updateRelatedTables(): void
    {
        Log::info('Actualizando foreign keys en tablas relacionadas');

        // Actualizar FK de payment_appointments para apuntar a nueva tabla payments
        Log::info('Actualizando FK en payment_appointments');

        // Eliminar FK viejo que apunta a payments_old
        DB::statement('ALTER TABLE payment_appointments DROP FOREIGN KEY payment_appointments_payment_id_foreign');

        // Crear FK nuevo apuntando a payments
        DB::statement('
            ALTER TABLE payment_appointments
            ADD CONSTRAINT payment_appointments_payment_id_foreign
            FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
        ');

        // Actualizar FK de liquidation_details para apuntar a nueva tabla payments
        Log::info('Actualizando FK en liquidation_details');

        // Eliminar FK viejo que apunta a payments_old
        DB::statement('ALTER TABLE liquidation_details DROP FOREIGN KEY liquidation_details_payment_id_foreign');

        // Crear FK nuevo apuntando a payments
        DB::statement('
            ALTER TABLE liquidation_details
            ADD CONSTRAINT liquidation_details_payment_id_foreign
            FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
        ');

        Log::info('Foreign keys actualizadas correctamente');
    }

    /**
     * Valida que la migración se haya realizado correctamente
     */
    private function validateMigration(): void
    {
        Log::info('Validando integridad de datos migrados');

        // Validación 1: Mismo número de pagos
        $oldCount = DB::table('payments_old')->count();
        $newCount = DB::table('payments')->count();

        if ($oldCount !== $newCount) {
            throw new \Exception("Discrepancia en conteo de pagos: OLD={$oldCount}, NEW={$newCount}");
        }
        Log::info("✓ Conteo de pagos: {$newCount}");

        // Validación 2: Todos los pagos tienen payment_details
        $paymentsWithoutDetails = DB::table('payments as p')
            ->leftJoin('payment_details as pd', 'p.id', '=', 'pd.payment_id')
            ->whereNull('pd.id')
            ->count();

        if ($paymentsWithoutDetails > 0) {
            throw new \Exception("Hay {$paymentsWithoutDetails} pagos sin payment_details");
        }
        Log::info('✓ Todos los pagos tienen payment_details');

        // Validación 3: Montos coinciden
        $amountMismatch = DB::table('payments as p')
            ->join(
                DB::raw('(SELECT payment_id, SUM(amount) as total FROM payment_details GROUP BY payment_id) as pd'),
                'p.id', '=', 'pd.payment_id'
            )
            ->whereRaw('ABS(p.total_amount - pd.total) > 0.01')
            ->count();

        if ($amountMismatch > 0) {
            throw new \Exception("Hay {$amountMismatch} pagos con discrepancia de montos");
        }
        Log::info('✓ Montos de pagos coinciden con payment_details');

        // Validación 4: Paquetes migrados correctamente
        $packagesInOld = DB::table('payments_old')
            ->where(function($q) {
                $q->where('payment_type', 'package')
                  ->orWhere('sessions_included', '>', 1);
            })
            ->count();

        $packagesInNew = DB::table('patient_packages')->count();

        if ($packagesInOld !== $packagesInNew) {
            throw new \Exception("Discrepancia en paquetes: OLD={$packagesInOld}, NEW={$packagesInNew}");
        }
        Log::info("✓ Paquetes migrados: {$packagesInNew}");

        // Validación 5: payment_appointments sin huérfanos
        $orphanedAssignments = DB::table('payment_appointments as pa')
            ->leftJoin('payments as p', 'pa.payment_id', '=', 'p.id')
            ->whereNull('p.id')
            ->count();

        if ($orphanedAssignments > 0) {
            throw new \Exception("Hay {$orphanedAssignments} payment_appointments huérfanos");
        }
        Log::info('✓ payment_appointments sin huérfanos');

        // Validación 6: liquidation_details sin huérfanos
        $orphanedLiquidations = DB::table('liquidation_details as ld')
            ->leftJoin('payments as p', 'ld.payment_id', '=', 'p.id')
            ->whereNull('p.id')
            ->count();

        if ($orphanedLiquidations > 0) {
            throw new \Exception("Hay {$orphanedLiquidations} liquidation_details huérfanos");
        }
        Log::info('✓ liquidation_details sin huérfanos');

        Log::info('=== TODAS LAS VALIDACIONES PASARON ===');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay rollback automático porque es muy peligroso
        // Se debe restaurar desde backup
        throw new \Exception('Esta migración no puede ser revertida automáticamente. Restaure desde backup.');
    }
};
