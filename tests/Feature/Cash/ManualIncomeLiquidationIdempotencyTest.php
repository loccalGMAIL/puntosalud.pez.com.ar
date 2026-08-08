<?php

namespace Tests\Feature\Cash;

use App\Models\CashMovement;
use App\Models\MovementType;
use App\Models\Professional;
use App\Models\ProfessionalLiquidation;
use App\Models\Profile;
use App\Models\ProfileModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualIncomeLiquidationIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function actingUserWithCashAccess(): void
    {
        $profile = Profile::create(['name' => 'Test Profile']);
        ProfileModule::create(['profile_id' => $profile->id, 'module' => 'cash']);

        $this->actingAs(User::factory()->create(['profile_id' => $profile->id, 'is_active' => true]));
    }

    private function openCashToday(): void
    {
        $openingType = MovementType::factory()->withCode('cash_opening')->create();
        CashMovement::factory()->create(['movement_type_id' => $openingType->id]);
    }

    public function test_resubmitting_same_liquidation_id_does_not_create_a_duplicate_cash_movement(): void
    {
        $this->actingUserWithCashAccess();
        $this->openCashToday();

        MovementType::factory()->withCode('professional_module_payment')
            ->create(['category' => 'income_detail', 'is_active' => true]);

        $professional = Professional::factory()->create();
        $liquidation = ProfessionalLiquidation::factory()->create([
            'professional_id' => $professional->id,
            'net_professional_amount' => -5000,
            'clinic_settlement_status' => 'pending',
        ]);

        $payload = [
            'amount' => 5000,
            'category' => 'professional_module_payment',
            'payment_method' => 'cash',
            'description' => 'Entrega al centro',
            'notes' => '',
            'professional_id' => $professional->id,
            'liquidation_id' => $liquidation->id,
        ];

        $headers = ['X-Requested-With' => 'XMLHttpRequest'];

        // Primer envío: se registra el ingreso y se salda la liquidación.
        $this->post('/cash/manual-income', $payload, $headers)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('cash_movements', 2); // apertura + este ingreso
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('settled', $liquidation->fresh()->clinic_settlement_status);

        // Reenvío con el mismo liquidation_id (doble click / reintento): debe rechazarse
        // sin crear un segundo Payment/CashMovement.
        $this->post('/cash/manual-income', $payload, $headers)
            ->assertStatus(409)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('cash_movements', 2);
        $this->assertDatabaseCount('payments', 1);
    }
}
