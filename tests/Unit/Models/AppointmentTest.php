<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PaymentAppointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    // ─── markAsAttended ────────────────────────────────────────────────────

    public function test_mark_as_attended_updates_status(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);

        $appointment->markAsAttended(8000);

        $this->assertEquals('attended', $appointment->fresh()->status);
    }

    public function test_mark_as_attended_sets_final_amount(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);

        $appointment->markAsAttended(8000);

        $this->assertEquals('8000.00', $appointment->fresh()->final_amount);
    }

    public function test_mark_as_attended_uses_estimated_amount_when_no_final_amount_given(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => 'scheduled',
            'estimated_amount' => 5000,
        ]);

        $appointment->markAsAttended();

        $this->assertEquals('5000.00', $appointment->fresh()->final_amount);
    }

    // ─── markAsAbsent ──────────────────────────────────────────────────────

    public function test_mark_as_absent_updates_status(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);

        $appointment->markAsAbsent();

        $this->assertEquals('absent', $appointment->fresh()->status);
    }

    public function test_mark_as_absent_sets_final_amount_to_zero(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => 'scheduled',
            'estimated_amount' => 5000,
        ]);

        $appointment->markAsAbsent();

        $this->assertEquals('0.00', $appointment->fresh()->final_amount);
    }

    // ─── cancel ────────────────────────────────────────────────────────────

    public function test_cancel_updates_status_to_cancelled(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);

        $appointment->cancel();

        $this->assertEquals('cancelled', $appointment->fresh()->status);
    }

    // ─── canBeCancelled ────────────────────────────────────────────────────

    public function test_can_be_cancelled_returns_true_for_scheduled_future_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => 'scheduled',
            'appointment_date' => now()->addDay(),
        ]);

        $this->assertTrue($appointment->canBeCancelled());
    }

    public function test_can_be_cancelled_returns_false_for_attended_appointment(): void
    {
        $appointment = Appointment::factory()->attended()->create();

        $this->assertFalse($appointment->canBeCancelled());
    }

    public function test_can_be_cancelled_returns_false_for_past_scheduled_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => 'scheduled',
            'appointment_date' => now()->subDay(),
        ]);

        $this->assertFalse($appointment->canBeCancelled());
    }

    public function test_can_be_cancelled_returns_false_for_cancelled_appointment(): void
    {
        $appointment = Appointment::factory()->cancelled()->create();

        $this->assertFalse($appointment->canBeCancelled());
    }

    // ─── conflictsWith ─────────────────────────────────────────────────────

    public function test_conflicts_with_returns_true_for_overlapping_appointments(): void
    {
        // Turno 10:00 - 10:45
        $appointment = Appointment::factory()->make([
            'appointment_date' => '2026-04-01 10:00:00',
            'duration' => 45,
        ]);

        // Nuevo turno 10:30 - 11:00 (se superpone)
        $newStart = \Carbon\Carbon::parse('2026-04-01 10:30:00');
        $newEnd = \Carbon\Carbon::parse('2026-04-01 11:00:00');

        $this->assertTrue($appointment->conflictsWith($newStart, $newEnd));
    }

    public function test_conflicts_with_returns_false_for_adjacent_appointments(): void
    {
        // Turno 10:00 - 10:30
        $appointment = Appointment::factory()->make([
            'appointment_date' => '2026-04-01 10:00:00',
            'duration' => 30,
        ]);

        // Nuevo turno 10:30 - 11:00 (adyacente, no se superpone)
        $newStart = \Carbon\Carbon::parse('2026-04-01 10:30:00');
        $newEnd = \Carbon\Carbon::parse('2026-04-01 11:00:00');

        $this->assertFalse($appointment->conflictsWith($newStart, $newEnd));
    }

    public function test_conflicts_with_returns_false_for_non_overlapping_appointments(): void
    {
        // Turno 10:00 - 10:30
        $appointment = Appointment::factory()->make([
            'appointment_date' => '2026-04-01 10:00:00',
            'duration' => 30,
        ]);

        // Nuevo turno 11:00 - 11:30 (sin superposición)
        $newStart = \Carbon\Carbon::parse('2026-04-01 11:00:00');
        $newEnd = \Carbon\Carbon::parse('2026-04-01 11:30:00');

        $this->assertFalse($appointment->conflictsWith($newStart, $newEnd));
    }

    public function test_conflicts_with_returns_true_for_same_time(): void
    {
        $appointment = Appointment::factory()->make([
            'appointment_date' => '2026-04-01 10:00:00',
            'duration' => 45,
        ]);

        $newStart = \Carbon\Carbon::parse('2026-04-01 10:00:00');
        $newEnd = \Carbon\Carbon::parse('2026-04-01 10:45:00');

        $this->assertTrue($appointment->conflictsWith($newStart, $newEnd));
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    public function test_end_time_is_appointment_date_plus_duration(): void
    {
        $appointment = Appointment::factory()->make([
            'appointment_date' => '2026-04-01 10:00:00',
            'duration' => 45,
        ]);

        $this->assertEquals('10:45', $appointment->end_time->format('H:i'));
    }

    public function test_pending_amount_is_zero_when_not_attended(): void
    {
        $appointment = Appointment::factory()->make(['status' => 'scheduled']);

        $this->assertEquals(0, $appointment->pending_amount);
    }

    public function test_is_urgency_returns_true_when_duration_is_zero(): void
    {
        $appointment = Appointment::factory()->make(['duration' => 0]);

        $this->assertTrue($appointment->is_urgency);
    }

    public function test_is_urgency_returns_false_when_duration_is_positive(): void
    {
        $appointment = Appointment::factory()->make(['duration' => 30]);

        $this->assertFalse($appointment->is_urgency);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function test_scope_scheduled_returns_only_scheduled(): void
    {
        Appointment::factory()->count(2)->create(['status' => 'scheduled']);
        Appointment::factory()->attended()->create();

        $this->assertCount(2, Appointment::scheduled()->get());
    }

    public function test_scope_attended_returns_only_attended(): void
    {
        Appointment::factory()->count(2)->attended()->create();
        Appointment::factory()->create(['status' => 'scheduled']);

        $this->assertCount(2, Appointment::attended()->get());
    }

    public function test_scope_completed_returns_attended_and_absent(): void
    {
        Appointment::factory()->attended()->create();
        Appointment::factory()->absent()->create();
        Appointment::factory()->create(['status' => 'scheduled']);

        $this->assertCount(2, Appointment::completed()->get());
    }

    public function test_scope_unpaid_returns_attended_without_payment_appointments(): void
    {
        $unpaid = Appointment::factory()->attended()->create();
        $paid = Appointment::factory()->attended()->create();

        PaymentAppointment::factory()->create([
            'appointment_id' => $paid->id,
            'professional_id' => $paid->professional_id,
        ]);

        $result = Appointment::unpaid()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($unpaid->id, $result->first()->id);
    }
}
