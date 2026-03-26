<?php

namespace Tests\Unit\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\Payment;
use App\Models\PaymentAppointment;
use App\Services\PaymentAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class PaymentAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentAllocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentAllocationService();
    }

    // ─── allocateSinglePayment ─────────────────────────────────────────────

    public function test_allocate_single_payment_creates_payment_appointment(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $pa = $this->service->allocateSinglePayment($payment->id, $appointment->id);

        $this->assertInstanceOf(PaymentAppointment::class, $pa);
        $this->assertEquals($payment->id, $pa->payment_id);
        $this->assertEquals($appointment->id, $pa->appointment_id);
        $this->assertTrue((bool) $pa->is_liquidation_trigger);
    }

    public function test_allocate_single_payment_sets_allocated_amount_to_total_amount(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->withAmount(8000)->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $pa = $this->service->allocateSinglePayment($payment->id, $appointment->id);

        $this->assertEquals(8000, $pa->allocated_amount);
    }

    public function test_allocate_single_payment_throws_if_not_single_type(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->refund()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->allocateSinglePayment($payment->id, $appointment->id);
    }

    public function test_allocate_single_payment_throws_if_appointment_not_attended(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->scheduled()->create(['patient_id' => $patient->id]);

        $this->expectException(RuntimeException::class);
        $this->service->allocateSinglePayment($payment->id, $appointment->id);
    }

    public function test_allocate_single_payment_throws_if_appointment_already_has_payment(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        PaymentAppointment::factory()->create([
            'appointment_id' => $appointment->id,
            'payment_id' => $payment->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->service->allocateSinglePayment($payment->id, $appointment->id);
    }

    public function test_allocate_single_payment_throws_if_patient_mismatch(): void
    {
        $payment = Payment::factory()->single()->create();
        $appointment = Appointment::factory()->attended()->create(); // paciente diferente

        $this->expectException(InvalidArgumentException::class);
        $this->service->allocateSinglePayment($payment->id, $appointment->id);
    }

    public function test_allocate_single_payment_throws_if_payment_already_used(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment1 = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        $appointment2 = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        // Primer uso exitoso
        $this->service->allocateSinglePayment($payment->id, $appointment1->id);

        // Segundo uso debe fallar
        $this->expectException(RuntimeException::class);
        $this->service->allocateSinglePayment($payment->id, $appointment2->id);
    }

    // ─── allocatePackageSession ────────────────────────────────────────────

    public function test_allocate_package_session_creates_payment_appointment(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(5, 0)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $pa = $this->service->allocatePackageSession($payment->id, $appointment->id);

        $this->assertInstanceOf(PaymentAppointment::class, $pa);
        $this->assertEquals($payment->id, $pa->payment_id);
    }

    public function test_allocate_package_session_amount_is_price_divided_by_sessions(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(5, 0)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $pa = $this->service->allocatePackageSession($payment->id, $appointment->id);

        $this->assertEquals(10000, $pa->allocated_amount); // 50000 / 5
    }

    public function test_allocate_package_session_first_session_is_liquidation_trigger(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(5, 0)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $pa = $this->service->allocatePackageSession($payment->id, $appointment->id);

        $this->assertTrue((bool) $pa->is_liquidation_trigger);
    }

    public function test_allocate_package_session_subsequent_sessions_are_not_liquidation_trigger(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(5, 1)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $pa = $this->service->allocatePackageSession($payment->id, $appointment->id);

        $this->assertFalse((bool) $pa->is_liquidation_trigger);
    }

    public function test_allocate_package_session_increments_sessions_used(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        $pkg = PatientPackage::factory()->withSessions(5, 2)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $this->service->allocatePackageSession($payment->id, $appointment->id);

        $this->assertEquals(3, $pkg->fresh()->sessions_used);
    }

    public function test_allocate_package_session_marks_payment_liquidated_on_last_session(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(3, 2)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 30000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $this->service->allocatePackageSession($payment->id, $appointment->id);

        $this->assertEquals('liquidated', $payment->fresh()->liquidation_status);
    }

    public function test_allocate_package_session_throws_if_no_sessions_remaining(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->completed()->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'sessions_included' => 3,
            'sessions_used' => 3,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $this->expectException(RuntimeException::class);
        $this->service->allocatePackageSession($payment->id, $appointment->id);
    }

    public function test_allocate_package_session_throws_if_package_not_active(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->expired()->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'sessions_included' => 5,
            'sessions_used' => 2,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $this->expectException(RuntimeException::class);
        $this->service->allocatePackageSession($payment->id, $appointment->id);
    }

    // ─── checkAndAllocatePayment ───────────────────────────────────────────

    public function test_check_and_allocate_returns_null_if_appointment_not_attended(): void
    {
        $appointment = Appointment::factory()->scheduled()->create();

        $result = $this->service->checkAndAllocatePayment($appointment->id);

        $this->assertNull($result);
    }

    public function test_check_and_allocate_returns_null_if_already_allocated(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        PaymentAppointment::factory()->create([
            'payment_id' => $payment->id,
            'appointment_id' => $appointment->id,
        ]);

        $result = $this->service->checkAndAllocatePayment($appointment->id);

        $this->assertNull($result);
    }

    public function test_check_and_allocate_uses_package_if_available(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(5, 0)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $result = $this->service->checkAndAllocatePayment($appointment->id);

        $this->assertNotNull($result);
        $this->assertEquals($payment->id, $result->payment_id);
    }

    public function test_check_and_allocate_falls_back_to_single_if_no_package(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);

        $result = $this->service->checkAndAllocatePayment($appointment->id);

        $this->assertNotNull($result);
        $this->assertEquals($payment->id, $result->payment_id);
    }

    public function test_check_and_allocate_returns_null_if_no_payment_available(): void
    {
        $appointment = Appointment::factory()->attended()->create();

        $result = $this->service->checkAndAllocatePayment($appointment->id);

        $this->assertNull($result);
    }

    // ─── deallocatePayment ─────────────────────────────────────────────────

    public function test_deallocate_deletes_payment_appointment(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        $pa = PaymentAppointment::factory()->create([
            'payment_id' => $payment->id,
            'appointment_id' => $appointment->id,
        ]);

        $this->service->deallocatePayment($pa->id);

        $this->assertNull(PaymentAppointment::find($pa->id));
    }

    public function test_deallocate_returns_session_to_package(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->create(['patient_id' => $patient->id]);
        $pkg = PatientPackage::factory()->withSessions(5, 3)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        $pa = PaymentAppointment::factory()->create([
            'payment_id' => $payment->id,
            'appointment_id' => $appointment->id,
        ]);

        $this->service->deallocatePayment($pa->id);

        $this->assertEquals(2, $pkg->fresh()->sessions_used);
    }

    // ─── getPaymentAllocationSummary ───────────────────────────────────────

    public function test_summary_returns_correct_structure_for_single_payment(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->withAmount(8000)->create(['patient_id' => $patient->id]);

        $summary = $this->service->getPaymentAllocationSummary($payment->id);

        $this->assertEquals(1, $summary['total_sessions']);
        $this->assertEquals(0, $summary['used_sessions']);
        $this->assertEquals(1, $summary['remaining_sessions']);
        $this->assertEquals(8000, $summary['total_amount']);
        $this->assertEquals(0, $summary['allocated_amount']);
        $this->assertFalse($summary['is_fully_allocated']);
    }

    public function test_summary_is_fully_allocated_when_single_payment_used(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->single()->withAmount(8000)->create(['patient_id' => $patient->id]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        PaymentAppointment::factory()->create([
            'payment_id' => $payment->id,
            'appointment_id' => $appointment->id,
            'allocated_amount' => 8000,
        ]);

        $summary = $this->service->getPaymentAllocationSummary($payment->id);

        $this->assertTrue($summary['is_fully_allocated']);
        $this->assertEquals(8000, $summary['allocated_amount']);
        $this->assertEquals(0, $summary['remaining_amount']);
    }

    public function test_summary_uses_package_sessions_for_package_purchase(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->withAmount(50000)->create(['patient_id' => $patient->id]);
        PatientPackage::factory()->withSessions(5, 2)->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'price_paid' => 50000,
        ]);

        $summary = $this->service->getPaymentAllocationSummary($payment->id);

        $this->assertEquals(5, $summary['total_sessions']);
        $this->assertEquals(2, $summary['used_sessions']);
        $this->assertEquals(3, $summary['remaining_sessions']);
        $this->assertFalse($summary['is_fully_allocated']);
    }

    // ─── deallocatePayment ─────────────────────────────────────────────────

    public function test_deallocate_restores_liquidation_status_when_package_has_remaining_sessions(): void
    {
        $patient = Patient::factory()->create();
        $payment = Payment::factory()->packagePurchase()->liquidated()->create(['patient_id' => $patient->id]);
        $pkg = PatientPackage::factory()->completed()->create([
            'patient_id' => $patient->id,
            'payment_id' => $payment->id,
            'sessions_included' => 5,
            'sessions_used' => 5,
        ]);
        $appointment = Appointment::factory()->attended()->create(['patient_id' => $patient->id]);
        $pa = PaymentAppointment::factory()->create([
            'payment_id' => $payment->id,
            'appointment_id' => $appointment->id,
        ]);

        $this->service->deallocatePayment($pa->id);

        $this->assertEquals('pending', $payment->fresh()->liquidation_status);
    }
}
