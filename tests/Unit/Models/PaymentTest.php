<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\Payment;
use App\Models\PaymentAppointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    // ─── generateReceiptNumber ─────────────────────────────────────────────

    public function test_generate_receipt_number_starts_at_000001(): void
    {
        $number = Payment::generateReceiptNumber();

        $this->assertEquals('000001', $number);
    }

    public function test_generate_receipt_number_is_sequential(): void
    {
        Payment::factory()->create(); // genera 000001

        $number = Payment::generateReceiptNumber();

        $this->assertEquals('000002', $number);
    }

    public function test_generate_receipt_number_pads_to_six_digits(): void
    {
        // Crear 9 pagos para forzar número 000010
        Payment::factory()->count(9)->create();

        $number = Payment::generateReceiptNumber();

        $this->assertEquals('000010', $number);
        $this->assertEquals(6, strlen($number));
    }

    public function test_receipt_number_is_auto_generated_on_creating(): void
    {
        $payment = Payment::factory()->create();

        $this->assertNotNull($payment->receipt_number);
        $this->assertEquals(6, strlen($payment->receipt_number));
    }

    public function test_manual_income_with_negative_amount_gets_receipt_number(): void
    {
        // 'expense' fue eliminado del enum payment_type; los egresos se registran
        // como 'manual_income' con total_amount negativo y sí generan receipt_number.
        $payment = Payment::factory()->negativeIncome()->create();

        $this->assertNotNull($payment->fresh()->receipt_number);
    }

    // ─── canBeUsedForAppointment ───────────────────────────────────────────

    public function test_single_payment_can_be_used_when_not_yet_allocated(): void
    {
        $payment = Payment::factory()->single()->create();

        $this->assertTrue($payment->canBeUsedForAppointment());
    }

    public function test_single_payment_cannot_be_used_when_already_allocated(): void
    {
        $payment = Payment::factory()->single()->create();

        PaymentAppointment::factory()->create(['payment_id' => $payment->id]);

        $this->assertFalse($payment->fresh()->canBeUsedForAppointment());
    }

    public function test_package_purchase_can_be_used_when_has_sessions(): void
    {
        $payment = Payment::factory()->packagePurchase()->create();
        PatientPackage::factory()->create([
            'payment_id' => $payment->id,
            'sessions_included' => 10,
            'sessions_used' => 3,
            'status' => 'active',
        ]);

        $this->assertTrue($payment->fresh()->canBeUsedForAppointment());
    }

    public function test_package_purchase_cannot_be_used_when_no_sessions_remaining(): void
    {
        $payment = Payment::factory()->packagePurchase()->create();
        PatientPackage::factory()->completed()->create([
            'payment_id' => $payment->id,
            'sessions_included' => 5,
            'sessions_used' => 5,
        ]);

        $this->assertFalse($payment->fresh()->canBeUsedForAppointment());
    }

    public function test_package_purchase_cannot_be_used_when_package_is_not_active(): void
    {
        $payment = Payment::factory()->packagePurchase()->create();
        PatientPackage::factory()->expired()->create([
            'payment_id' => $payment->id,
        ]);

        $this->assertFalse($payment->fresh()->canBeUsedForAppointment());
    }

    public function test_refund_payment_cannot_be_used_for_appointment(): void
    {
        $payment = Payment::factory()->refund()->create();

        $this->assertFalse($payment->canBeUsedForAppointment());
    }

    // ─── markAsLiquidated ──────────────────────────────────────────────────

    public function test_mark_as_liquidated_updates_liquidation_status(): void
    {
        $payment = Payment::factory()->create(['liquidation_status' => 'pending']);

        $payment->markAsLiquidated();

        $this->assertEquals('liquidated', $payment->fresh()->liquidation_status);
    }

    public function test_mark_as_liquidated_sets_liquidated_at(): void
    {
        $payment = Payment::factory()->create();

        $payment->markAsLiquidated();

        $this->assertNotNull($payment->fresh()->liquidated_at);
    }

    // ─── cancel / confirm ──────────────────────────────────────────────────

    public function test_cancel_updates_status_to_cancelled(): void
    {
        $payment = Payment::factory()->create(['status' => 'confirmed']);

        $payment->cancel();

        $this->assertEquals('cancelled', $payment->fresh()->status);
    }

    public function test_confirm_updates_status_to_confirmed(): void
    {
        $payment = Payment::factory()->create(['status' => 'pending']);

        $payment->confirm();

        $this->assertEquals('confirmed', $payment->fresh()->status);
    }

    // ─── getTotalReceivedBy ────────────────────────────────────────────────

    public function test_get_total_received_by_centro_sums_correctly(): void
    {
        $payment = Payment::factory()->create();

        \App\Models\PaymentDetail::factory()->create([
            'payment_id' => $payment->id,
            'amount' => 3000,
            'received_by' => 'centro',
        ]);
        \App\Models\PaymentDetail::factory()->create([
            'payment_id' => $payment->id,
            'amount' => 2000,
            'received_by' => 'profesional',
        ]);

        $this->assertEquals(3000, $payment->getTotalReceivedByCentro());
    }

    public function test_get_total_received_by_profesional_sums_correctly(): void
    {
        $payment = Payment::factory()->create();

        \App\Models\PaymentDetail::factory()->create([
            'payment_id' => $payment->id,
            'amount' => 4000,
            'received_by' => 'profesional',
        ]);
        \App\Models\PaymentDetail::factory()->create([
            'payment_id' => $payment->id,
            'amount' => 1000,
            'received_by' => 'centro',
        ]);

        $this->assertEquals(4000, $payment->getTotalReceivedByProfesional());
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function test_scope_pending_returns_only_pending_liquidation_status(): void
    {
        Payment::factory()->create(['liquidation_status' => 'pending']);
        Payment::factory()->liquidated()->create();

        $this->assertCount(1, Payment::pending()->get());
    }

    public function test_scope_liquidated_returns_only_liquidated(): void
    {
        Payment::factory()->create(['liquidation_status' => 'pending']);
        Payment::factory()->liquidated()->create();
        Payment::factory()->liquidated()->create();

        $this->assertCount(2, Payment::liquidated()->get());
    }

    public function test_scope_single_payments_returns_only_single_type(): void
    {
        Payment::factory()->single()->count(2)->create();
        Payment::factory()->packagePurchase()->create();

        $this->assertCount(2, Payment::singlePayments()->get());
    }

    public function test_scope_packages_returns_only_package_purchases(): void
    {
        Payment::factory()->packagePurchase()->count(3)->create();
        Payment::factory()->single()->create();

        $this->assertCount(3, Payment::packages()->get());
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    public function test_is_cancelled_returns_true_when_cancelled(): void
    {
        $payment = Payment::factory()->cancelled()->make();

        $this->assertTrue($payment->is_cancelled);
    }

    public function test_is_package_purchase_returns_true_for_package_type(): void
    {
        $payment = Payment::factory()->packagePurchase()->make();

        $this->assertTrue($payment->is_package_purchase);
    }

    public function test_amount_attribute_is_alias_for_total_amount(): void
    {
        $payment = Payment::factory()->withAmount(7500)->make();

        $this->assertEquals(7500, $payment->amount);
    }
}
