<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFingerprintTest extends TestCase
{
    use RefreshDatabase;

    private function fingerprint(): string
    {
        return $this->getJson(route('dashboard.fingerprint'))
            ->assertOk()
            ->json('fingerprint');
    }

    public function test_guest_cannot_access_fingerprint(): void
    {
        $this->getJson(route('dashboard.fingerprint'))->assertUnauthorized();
    }

    public function test_fingerprint_is_stable_without_changes(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));
        Appointment::factory()->atTime(now()->format('Y-m-d H:i:s'))->create();

        $this->assertSame($this->fingerprint(), $this->fingerprint());
    }

    public function test_fingerprint_changes_when_a_patient_is_updated(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $patient = Patient::factory()->create();

        $before = $this->fingerprint();

        $this->travel(2)->seconds();
        $patient->update(['phone' => '1155556666']);

        $this->assertNotSame($before, $this->fingerprint());
    }

    public function test_fingerprint_changes_when_todays_appointment_status_changes(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $appointment = Appointment::factory()->atTime(now()->format('Y-m-d H:i:s'))->create();

        $before = $this->fingerprint();

        $this->travel(2)->seconds();
        $appointment->update(['status' => 'attended']);

        $this->assertNotSame($before, $this->fingerprint());
    }
}
