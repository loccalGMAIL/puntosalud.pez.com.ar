<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalTest extends TestCase
{
    use RefreshDatabase;

    // ─── calculateCommission ───────────────────────────────────────────────

    public function test_calculate_commission_applies_percentage_correctly(): void
    {
        $professional = Professional::factory()->withCommission(15)->make();

        $this->assertEquals(1500.00, $professional->calculateCommission(10000));
    }

    public function test_calculate_commission_with_zero_percentage(): void
    {
        $professional = Professional::factory()->withCommission(0)->make();

        $this->assertEquals(0.0, $professional->calculateCommission(10000));
    }

    public function test_calculate_commission_with_hundred_percentage(): void
    {
        $professional = Professional::factory()->withCommission(100)->make();

        $this->assertEquals(10000.0, $professional->calculateCommission(10000));
    }

    public function test_calculate_commission_with_decimal_amount(): void
    {
        $professional = Professional::factory()->withCommission(20)->make();

        $this->assertEquals(1600.0, $professional->calculateCommission(8000));
    }

    // ─── getClinicAmount ───────────────────────────────────────────────────

    public function test_get_clinic_amount_subtracts_commission(): void
    {
        $professional = Professional::factory()->withCommission(20)->make();

        $this->assertEquals(8000.0, $professional->getClinicAmount(10000));
    }

    public function test_get_clinic_amount_with_zero_commission_returns_full_amount(): void
    {
        $professional = Professional::factory()->withCommission(0)->make();

        $this->assertEquals(10000.0, $professional->getClinicAmount(10000));
    }

    public function test_get_clinic_amount_equals_amount_minus_commission(): void
    {
        $professional = Professional::factory()->withCommission(25)->make();
        $amount = 12000;

        $expected = $amount - $professional->calculateCommission($amount);

        $this->assertEquals($expected, $professional->getClinicAmount($amount));
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    public function test_full_name_concatenates_first_and_last_name(): void
    {
        $professional = Professional::factory()->make([
            'first_name' => 'Carlos',
            'last_name' => 'Rodríguez',
        ]);

        $this->assertEquals('Carlos Rodríguez', $professional->full_name);
    }

    public function test_formatted_commission_includes_percent_sign(): void
    {
        $professional = Professional::factory()->withCommission(15)->make();

        $this->assertStringContainsString('%', $professional->formatted_commission);
        $this->assertStringContainsString('15', $professional->formatted_commission);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function test_scope_active_returns_only_active_professionals(): void
    {
        Professional::factory()->count(3)->create();
        Professional::factory()->inactive()->count(2)->create();

        $active = Professional::active()->get();

        $this->assertCount(3, $active);
        $active->each(fn ($p) => $this->assertTrue($p->is_active));
    }

    public function test_scope_ordered_sorts_by_last_name_then_first_name(): void
    {
        Professional::factory()->create(['last_name' => 'Zapata', 'first_name' => 'Ana']);
        Professional::factory()->create(['last_name' => 'García', 'first_name' => 'Pedro']);
        Professional::factory()->create(['last_name' => 'García', 'first_name' => 'Ana']);

        $professionals = Professional::ordered()->get();

        $this->assertEquals('García', $professionals[0]->last_name);
        $this->assertEquals('Ana', $professionals[0]->first_name);
        $this->assertEquals('García', $professionals[1]->last_name);
        $this->assertEquals('Pedro', $professionals[1]->first_name);
        $this->assertEquals('Zapata', $professionals[2]->last_name);
    }

    // ─── hasAppointmentAt ──────────────────────────────────────────────────

    public function test_has_appointment_at_returns_true_when_scheduled_exists(): void
    {
        $professional = Professional::factory()->create();
        $date = now()->addDay()->format('Y-m-d H:i:s');

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'appointment_date' => $date,
            'status' => 'scheduled',
        ]);

        $this->assertTrue($professional->hasAppointmentAt($date));
    }

    public function test_has_appointment_at_returns_false_when_cancelled(): void
    {
        $professional = Professional::factory()->create();
        $date = now()->addDay()->format('Y-m-d H:i:s');

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'appointment_date' => $date,
            'status' => 'cancelled',
        ]);

        $this->assertFalse($professional->hasAppointmentAt($date));
    }

    public function test_has_appointment_at_returns_false_when_no_appointment(): void
    {
        $professional = Professional::factory()->create();

        $this->assertFalse($professional->hasAppointmentAt(now()->addDay()->format('Y-m-d H:i:s')));
    }
}
