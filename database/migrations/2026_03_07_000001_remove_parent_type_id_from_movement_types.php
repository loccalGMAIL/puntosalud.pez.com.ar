<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Reasignar refund a expense_detail (ya no es main_type)
        DB::table('movement_types')
            ->where('code', 'refund')
            ->update(['category' => 'expense_detail']);

        // 2. Reasignar movimientos de los contenedores antes de borrarlos
        $expenseId        = DB::table('movement_types')->where('code', 'expense')->value('id');
        $otherId          = DB::table('movement_types')->where('code', 'other')->value('id');
        $cashWithdrawalId = DB::table('movement_types')->where('code', 'cash_withdrawal')->value('id');
        $otherExpenseId   = DB::table('movement_types')->where('code', 'other_expense')->value('id');
        $otherIncomeId    = DB::table('movement_types')->where('code', 'other_income')->value('id');
        $otherWithdrawalId = DB::table('movement_types')->where('code', 'other_withdrawal')->value('id');

        // expense container → other_expense
        if ($expenseId && $otherExpenseId) {
            DB::table('cash_movements')
                ->where('movement_type_id', $expenseId)
                ->update(['movement_type_id' => $otherExpenseId]);
        }

        // cash_withdrawal container → other_withdrawal
        if ($cashWithdrawalId && $otherWithdrawalId) {
            DB::table('cash_movements')
                ->where('movement_type_id', $cashWithdrawalId)
                ->update(['movement_type_id' => $otherWithdrawalId]);
        }

        // other container: positivos → other_income, negativos → other_expense
        if ($otherId) {
            if ($otherIncomeId) {
                DB::table('cash_movements')
                    ->where('movement_type_id', $otherId)
                    ->where('amount', '>=', 0)
                    ->update(['movement_type_id' => $otherIncomeId]);
            }
            if ($otherExpenseId) {
                DB::table('cash_movements')
                    ->where('movement_type_id', $otherId)
                    ->where('amount', '<', 0)
                    ->update(['movement_type_id' => $otherExpenseId]);
            }
        }

        // 3. Quitar parent_type_id de los hijos de los contenedores (ya no se usa)
        DB::table('movement_types')->whereNotNull('parent_type_id')->update(['parent_type_id' => null]);

        // 4. Eliminar los 3 contenedores organizacionales
        DB::table('movement_types')->whereIn('code', ['expense', 'other', 'cash_withdrawal'])->delete();

        // 5. Eliminar columna parent_type_id
        Schema::table('movement_types', function (Blueprint $table) {
            $table->dropForeign(['parent_type_id']);
            $table->dropIndex(['parent_type_id']);
            $table->dropColumn('parent_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('movement_types', function (Blueprint $table) {
            $table->foreignId('parent_type_id')->nullable()->constrained('movement_types')->onDelete('cascade');
            $table->index('parent_type_id');
        });
    }
};
