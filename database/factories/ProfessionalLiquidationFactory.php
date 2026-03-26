<?php

namespace Database\Factories;

use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessionalLiquidationFactory extends Factory
{
    public function definition(): array
    {
        $totalCollected = fake()->randomFloat(2, 10000, 100000);
        $directPayments = fake()->randomFloat(2, 0, $totalCollected * 0.3);
        $commissionPct = fake()->randomFloat(2, 20, 50);
        $professionalCommission = round($totalCollected * ($commissionPct / 100), 2);
        $clinicAmount = round($totalCollected - $professionalCommission, 2);

        return [
            'professional_id' => Professional::factory(),
            'liquidation_date' => now()->toDateString(),
            'sheet_type' => fake()->randomElement(['arrival', 'liquidation']),
            'appointments_total' => fake()->numberBetween(5, 30),
            'appointments_attended' => fake()->numberBetween(3, 25),
            'appointments_absent' => fake()->numberBetween(0, 5),
            'total_collected' => $totalCollected,
            'direct_payments_total' => $directPayments,
            'professional_commission' => $professionalCommission,
            'clinic_amount' => $clinicAmount,
            'clinic_amount_from_direct' => 0,
            'net_professional_amount' => $professionalCommission,
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'paid_at' => null,
            'paid_by' => null,
            'notes' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'payment_status' => 'paid',
            'payment_method' => fake()->randomElement(['cash', 'transfer']),
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(['payment_status' => 'pending']);
    }
}
