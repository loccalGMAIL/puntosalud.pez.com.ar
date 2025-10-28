<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\MovementType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Paso 1: Agregar nueva columna movement_type_id (nullable inicialmente)
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreignId('movement_type_id')
                ->nullable()
                ->after('type')
                ->constrained('movement_types')
                ->onDelete('restrict')
                ->comment('Referencia al tipo de movimiento');
        });

        // Paso 2: Migrar datos existentes del campo 'type' a 'movement_type_id'
        $this->migrateExistingData();

        // Paso 3: Limpiar y normalizar reference_type
        $this->cleanReferenceType();

        // Paso 4: Hacer el campo movement_type_id NOT NULL
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreignId('movement_type_id')->nullable(false)->change();
        });

        // Paso 5: Eliminar el campo 'type' antiguo
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Migrar datos existentes del campo 'type' (string) al nuevo 'movement_type_id' (FK)
     */
    private function migrateExistingData(): void
    {
        $cashMovements = DB::table('cash_movements')->get();

        foreach ($cashMovements as $movement) {
            // Buscar el tipo correspondiente por código
            $movementType = DB::table('movement_types')
                ->where('code', $movement->type)
                ->first();

            if ($movementType) {
                // Asignar el ID del tipo encontrado
                DB::table('cash_movements')
                    ->where('id', $movement->id)
                    ->update(['movement_type_id' => $movementType->id]);
            } else {
                // Si no se encuentra el tipo, asignar al tipo 'other' por defecto
                $otherType = DB::table('movement_types')
                    ->where('code', 'other')
                    ->first();

                if ($otherType) {
                    DB::table('cash_movements')
                        ->where('id', $movement->id)
                        ->update(['movement_type_id' => $otherType->id]);

                    \Log::warning("Movimiento ID {$movement->id} con tipo '{$movement->type}' no encontrado, asignado a 'other'");
                }
            }
        }
    }

    /**
     * Normalizar el campo reference_type para que solo contenga clases de modelos válidos
     */
    private function cleanReferenceType(): void
    {
        // Mapeo de valores antiguos a clases de modelos válidos
        $referenceTypeMapping = [
            'payment' => 'App\\Models\\Payment',
            'professional' => 'App\\Models\\Professional',
            'App\\Models\\Professional' => 'App\\Models\\Professional', // Ya está correcto
            'App\\Models\\Payment' => 'App\\Models\\Payment', // Ya está correcto
            'App\\Models\\ProfessionalLiquidation' => 'App\\Models\\ProfessionalLiquidation', // Ya está correcto
        ];

        // Valores que se deben convertir a NULL porque no son referencias a modelos
        $nullableValues = [
            'expense_category',
            'cash_opening',
            'cash_closing',
            'manual_income',
            'expense',
            'refund',
        ];

        foreach ($referenceTypeMapping as $oldValue => $newValue) {
            DB::table('cash_movements')
                ->where('reference_type', $oldValue)
                ->update(['reference_type' => $newValue]);
        }

        // Convertir valores no válidos a NULL
        DB::table('cash_movements')
            ->whereIn('reference_type', $nullableValues)
            ->update([
                'reference_type' => null,
                'reference_id' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Paso 1: Restaurar el campo 'type' desde movement_type_id
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('movement_date');
        });

        // Paso 2: Restaurar los valores de 'type' desde la tabla movement_types
        $cashMovements = DB::table('cash_movements')->get();

        foreach ($cashMovements as $movement) {
            if ($movement->movement_type_id) {
                $movementType = DB::table('movement_types')
                    ->where('id', $movement->movement_type_id)
                    ->first();

                if ($movementType) {
                    DB::table('cash_movements')
                        ->where('id', $movement->id)
                        ->update(['type' => $movementType->code]);
                }
            }
        }

        // Paso 3: Hacer 'type' NOT NULL
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->string('type', 50)->nullable(false)->change();
        });

        // Paso 4: Eliminar la columna movement_type_id
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['movement_type_id']);
            $table->dropColumn('movement_type_id');
        });
    }
};
