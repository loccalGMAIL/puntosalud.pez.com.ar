<?php

namespace Tests\Unit\Models;

use App\Models\ProfessionalLiquidation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalLiquidationTest extends TestCase
{
    use RefreshDatabase;

    // ─── isPaid / isPending ────────────────────────────────────────────────

    public function test_is_paid_returns_true_when_payment_status_is_paid(): void
    {
        $liquidation = ProfessionalLiquidation::factory()->paid()->make();

        $this->assertTrue($liquidation->isPaid());
    }

    public function test_is_paid_returns_false_when_pending(): void
    {
        $liquidation = ProfessionalLiquidation::factory()->pending()->make();

        $this->assertFalse($liquidation->isPaid());
    }

    public function test_is_pending_returns_true_when_payment_status_is_pending(): void
    {
        $liquidation = ProfessionalLiquidation::factory()->pending()->make();

        $this->assertTrue($liquidation->isPending());
    }

    public function test_is_pending_returns_false_when_paid(): void
    {
        $liquidation = ProfessionalLiquidation::factory()->paid()->make();

        $this->assertFalse($liquidation->isPending());
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function test_scope_pending_returns_only_pending(): void
    {
        ProfessionalLiquidation::factory()->pending()->count(2)->create();
        ProfessionalLiquidation::factory()->paid()->create();

        $this->assertCount(2, ProfessionalLiquidation::pending()->get());
    }

    public function test_scope_paid_returns_only_paid(): void
    {
        ProfessionalLiquidation::factory()->pending()->create();
        ProfessionalLiquidation::factory()->paid()->count(3)->create();

        $this->assertCount(3, ProfessionalLiquidation::paid()->get());
    }

    public function test_scope_by_type_filters_by_sheet_type(): void
    {
        ProfessionalLiquidation::factory()->create(['sheet_type' => 'arrival']);
        ProfessionalLiquidation::factory()->create(['sheet_type' => 'arrival']);
        ProfessionalLiquidation::factory()->create(['sheet_type' => 'liquidation']);

        $this->assertCount(2, ProfessionalLiquidation::byType('arrival')->get());
    }

    public function test_scope_by_date_filters_by_liquidation_date(): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        ProfessionalLiquidation::factory()->create(['liquidation_date' => $today]);
        ProfessionalLiquidation::factory()->create(['liquidation_date' => $today]);
        ProfessionalLiquidation::factory()->create(['liquidation_date' => $yesterday]);

        $this->assertCount(2, ProfessionalLiquidation::byDate($today)->get());
    }

    // ─── Relaciones ────────────────────────────────────────────────────────

    public function test_has_professional_relation(): void
    {
        $liquidation = ProfessionalLiquidation::factory()->create();

        $this->assertNotNull($liquidation->professional);
    }

    public function test_has_details_relation(): void
    {
        $liquidation = ProfessionalLiquidation::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $liquidation->details);
    }
}
