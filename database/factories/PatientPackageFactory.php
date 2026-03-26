<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PatientPackage>
 */
class PatientPackageFactory extends Factory
{
    public function definition(): array
    {
        $sessionsIncluded = fake()->randomElement([5, 8, 10]);

        return [
            'patient_id' => Patient::factory(),
            'package_id' => Package::factory(),
            'payment_id' => Payment::factory()->packagePurchase(),
            'sessions_included' => $sessionsIncluded,
            'sessions_used' => 0,
            'price_paid' => $sessionsIncluded * 10000,
            'purchase_date' => now()->toDateString(),
            'expires_at' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'sessions_used' => $attributes['sessions_included'],
            ];
        });
    }

    public function expired(): static
    {
        return $this->state([
            'status' => 'expired',
            'expires_at' => now()->subDays(10)->toDateString(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function withSessions(int $included, int $used = 0): static
    {
        return $this->state([
            'sessions_included' => $included,
            'sessions_used' => $used,
        ]);
    }

    public function noExpiry(): static
    {
        return $this->state(['expires_at' => null]);
    }
}
