<?php

namespace Tests\Unit\Models;

use App\Models\PatientPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPackageTest extends TestCase
{
    use RefreshDatabase;

    // ─── useSession ────────────────────────────────────────────────────────

    public function test_use_session_increments_sessions_used(): void
    {
        $package = PatientPackage::factory()->withSessions(10, 3)->create();

        $package->useSession();

        $this->assertEquals(4, $package->fresh()->sessions_used);
    }

    public function test_use_session_marks_as_completed_on_last_session(): void
    {
        $package = PatientPackage::factory()->withSessions(5, 4)->create();

        $package->useSession();

        $this->assertEquals('completed', $package->fresh()->status);
    }

    public function test_use_session_returns_false_when_no_sessions_remaining(): void
    {
        $package = PatientPackage::factory()->completed()->create();

        $result = $package->useSession();

        $this->assertFalse($result);
    }

    public function test_use_session_does_not_increment_when_already_completed(): void
    {
        $package = PatientPackage::factory()->withSessions(5, 5)->create(['status' => 'completed']);
        $originalUsed = $package->sessions_used;

        $package->useSession();

        $this->assertEquals($originalUsed, $package->fresh()->sessions_used);
    }

    // ─── returnSession ─────────────────────────────────────────────────────

    public function test_return_session_decrements_sessions_used(): void
    {
        $package = PatientPackage::factory()->withSessions(10, 5)->create();

        $package->returnSession();

        $this->assertEquals(4, $package->fresh()->sessions_used);
    }

    public function test_return_session_reactivates_completed_package(): void
    {
        $package = PatientPackage::factory()->completed()->create(['sessions_included' => 5, 'sessions_used' => 5]);

        $package->returnSession();

        $this->assertEquals('active', $package->fresh()->status);
        $this->assertEquals(4, $package->fresh()->sessions_used);
    }

    public function test_return_session_returns_false_when_sessions_used_is_zero(): void
    {
        $package = PatientPackage::factory()->withSessions(5, 0)->create();

        $result = $package->returnSession();

        $this->assertFalse($result);
    }

    // ─── cancel / markAsExpired ────────────────────────────────────────────

    public function test_cancel_sets_status_to_cancelled(): void
    {
        $package = PatientPackage::factory()->create();

        $package->cancel();

        $this->assertEquals('cancelled', $package->fresh()->status);
    }

    public function test_mark_as_expired_sets_status_to_expired(): void
    {
        $package = PatientPackage::factory()->create();

        $package->markAsExpired();

        $this->assertEquals('expired', $package->fresh()->status);
    }

    // ─── checkExpiration ───────────────────────────────────────────────────

    public function test_check_expiration_marks_expired_active_package(): void
    {
        $package = PatientPackage::factory()->create([
            'status' => 'active',
            'expires_at' => now()->subDay()->toDateString(),
        ]);

        $result = $package->checkExpiration();

        $this->assertTrue($result);
        $this->assertEquals('expired', $package->fresh()->status);
    }

    public function test_check_expiration_does_nothing_when_not_expired(): void
    {
        $package = PatientPackage::factory()->create([
            'status' => 'active',
            'expires_at' => now()->addMonth()->toDateString(),
        ]);

        $result = $package->checkExpiration();

        $this->assertFalse($result);
        $this->assertEquals('active', $package->fresh()->status);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    public function test_sessions_remaining_is_included_minus_used(): void
    {
        $package = PatientPackage::factory()->withSessions(10, 3)->make();

        $this->assertEquals(7, $package->sessions_remaining);
    }

    public function test_sessions_remaining_is_never_negative(): void
    {
        $package = PatientPackage::factory()->withSessions(5, 5)->make();

        $this->assertEquals(0, $package->sessions_remaining);
    }

    public function test_usage_percentage_calculates_correctly(): void
    {
        $package = PatientPackage::factory()->withSessions(10, 5)->make();

        $this->assertEquals(50.0, $package->usage_percentage);
    }

    public function test_usage_percentage_returns_zero_when_no_sessions_included(): void
    {
        $package = PatientPackage::factory()->withSessions(0, 0)->make();

        $this->assertEquals(0, $package->usage_percentage);
    }

    public function test_is_expired_returns_true_when_expires_at_is_in_past(): void
    {
        $package = PatientPackage::factory()->make([
            'expires_at' => now()->subDay()->toDateString(),
        ]);

        $this->assertTrue($package->is_expired);
    }

    public function test_is_expired_returns_false_when_expires_at_is_in_future(): void
    {
        $package = PatientPackage::factory()->make([
            'expires_at' => now()->addMonth()->toDateString(),
        ]);

        $this->assertFalse($package->is_expired);
    }

    public function test_is_expired_returns_false_when_no_expiry(): void
    {
        $package = PatientPackage::factory()->noExpiry()->make();

        $this->assertFalse($package->is_expired);
    }

    public function test_days_until_expiration_returns_correct_days(): void
    {
        $package = PatientPackage::factory()->make([
            'expires_at' => now()->addDays(15)->toDateString(),
        ]);

        // diffInDays puede ser 14 o 15 dependiendo de la hora de ejecución
        $this->assertEqualsWithDelta(15, $package->days_until_expiration, 1);
    }

    public function test_days_until_expiration_returns_null_when_no_expiry(): void
    {
        $package = PatientPackage::factory()->noExpiry()->make();

        $this->assertNull($package->days_until_expiration);
    }

    public function test_price_per_session_calculates_correctly(): void
    {
        $package = PatientPackage::factory()->withSessions(10, 0)->make([
            'price_paid' => 100000,
        ]);

        $this->assertEquals(10000.0, $package->price_per_session);
    }

    public function test_price_per_session_returns_zero_when_no_sessions(): void
    {
        $package = PatientPackage::factory()->withSessions(0, 0)->make([
            'price_paid' => 0,
        ]);

        $this->assertEquals(0, $package->price_per_session);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function test_scope_active_returns_only_active(): void
    {
        PatientPackage::factory()->active()->count(2)->create();
        PatientPackage::factory()->completed()->create();
        PatientPackage::factory()->expired()->create();

        $this->assertCount(2, PatientPackage::active()->get());
    }

    public function test_scope_with_available_sessions_returns_active_with_remaining_sessions(): void
    {
        PatientPackage::factory()->withSessions(10, 3)->create(['status' => 'active']);
        PatientPackage::factory()->completed()->create();

        $this->assertCount(1, PatientPackage::withAvailableSessions()->get());
    }
}
