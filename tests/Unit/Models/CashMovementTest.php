<?php

namespace Tests\Unit\Models;

use App\Models\CashMovement;
use App\Models\MovementType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashMovementTest extends TestCase
{
    use RefreshDatabase;

    // ─── isIncome / isExpense ──────────────────────────────────────────────

    public function test_is_income_returns_true_for_positive_amount(): void
    {
        $movement = CashMovement::factory()->withAmount(5000)->make();

        $this->assertTrue($movement->isIncome());
    }

    public function test_is_income_returns_false_for_negative_amount(): void
    {
        $movement = CashMovement::factory()->withAmount(-3000)->make();

        $this->assertFalse($movement->isIncome());
    }

    public function test_is_expense_returns_true_for_negative_amount(): void
    {
        $movement = CashMovement::factory()->withAmount(-3000)->make();

        $this->assertTrue($movement->isExpense());
    }

    public function test_is_expense_returns_false_for_positive_amount(): void
    {
        $movement = CashMovement::factory()->withAmount(5000)->make();

        $this->assertFalse($movement->isExpense());
    }

    // ─── isOpening / isClosing ─────────────────────────────────────────────

    public function test_is_opening_returns_true_for_cash_opening_type(): void
    {
        $type = MovementType::factory()->withCode('cash_opening')->create();
        $movement = CashMovement::factory()->make(['movement_type_id' => $type->id]);
        $movement->setRelation('movementType', $type);

        $this->assertTrue($movement->isOpening());
    }

    public function test_is_opening_returns_false_for_other_type(): void
    {
        $type = MovementType::factory()->withCode('patient_payment')->create();
        $movement = CashMovement::factory()->make(['movement_type_id' => $type->id]);
        $movement->setRelation('movementType', $type);

        $this->assertFalse($movement->isOpening());
    }

    public function test_is_closing_returns_true_for_cash_closing_type(): void
    {
        $type = MovementType::factory()->withCode('cash_closing')->create();
        $movement = CashMovement::factory()->make(['movement_type_id' => $type->id]);
        $movement->setRelation('movementType', $type);

        $this->assertTrue($movement->isClosing());
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function test_scope_income_returns_only_positive_amounts(): void
    {
        CashMovement::factory()->withAmount(5000)->create();
        CashMovement::factory()->withAmount(-2000)->create();
        CashMovement::factory()->withAmount(8000)->create();

        $this->assertCount(2, CashMovement::income()->get());
    }

    public function test_scope_expense_returns_only_negative_amounts(): void
    {
        CashMovement::factory()->withAmount(5000)->create();
        CashMovement::factory()->withAmount(-2000)->create();

        $this->assertCount(1, CashMovement::expense()->get());
    }

    public function test_scope_for_date_returns_movements_of_that_day(): void
    {
        CashMovement::factory()->create(); // hoy
        CashMovement::factory()->create(['created_at' => now()->subDay()]);

        $this->assertCount(1, CashMovement::forDate(now()->toDateString())->get());
    }

    // ─── getCashStatusForDate ──────────────────────────────────────────────

    public function test_get_cash_status_needs_opening_when_no_movements(): void
    {
        $status = CashMovement::getCashStatusForDate(now());

        $this->assertTrue($status['needs_opening']);
        $this->assertFalse($status['is_open']);
        $this->assertFalse($status['is_closed']);
    }

    public function test_get_cash_status_is_open_after_opening_movement(): void
    {
        $openingType = MovementType::factory()->withCode('cash_opening')->create();
        CashMovement::factory()->create(['movement_type_id' => $openingType->id]);

        $status = CashMovement::getCashStatusForDate(now());

        $this->assertTrue($status['is_open']);
        $this->assertFalse($status['is_closed']);
        $this->assertFalse($status['needs_opening']);
    }

    public function test_get_cash_status_is_closed_after_closing_movement(): void
    {
        $openingType = MovementType::factory()->withCode('cash_opening')->create();
        $closingType = MovementType::factory()->withCode('cash_closing')->create();
        CashMovement::factory()->create(['movement_type_id' => $openingType->id]);
        CashMovement::factory()->create(['movement_type_id' => $closingType->id]);

        $status = CashMovement::getCashStatusForDate(now());

        $this->assertTrue($status['is_closed']);
        $this->assertFalse($status['is_open']);
    }

    // ─── isCashOpenToday ───────────────────────────────────────────────────

    public function test_is_cash_open_today_returns_false_when_not_opened(): void
    {
        $this->assertFalse(CashMovement::isCashOpenToday());
    }

    public function test_is_cash_open_today_returns_true_when_opened(): void
    {
        $openingType = MovementType::factory()->withCode('cash_opening')->create();
        CashMovement::factory()->create(['movement_type_id' => $openingType->id]);

        $this->assertTrue(CashMovement::isCashOpenToday());
    }

    // ─── getCurrentBalanceWithLock ─────────────────────────────────────────

    public function test_get_current_balance_returns_zero_when_no_movements(): void
    {
        $this->assertEquals(0, CashMovement::getCurrentBalanceWithLock());
    }

    public function test_get_current_balance_returns_last_balance_after(): void
    {
        CashMovement::factory()->withBalance(10000)->create(['created_at' => now()->subMinutes(5)]);
        CashMovement::factory()->withBalance(15000)->create(['created_at' => now()]);

        $this->assertEquals(15000, CashMovement::getCurrentBalanceWithLock());
    }
}
