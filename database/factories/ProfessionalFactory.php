<?php

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Professional>
 */
class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'specialty_id' => Specialty::factory(),
            'dni' => fake()->unique()->numerify('##.###.###'),
            'license_number' => fake()->optional()->numerify('MP ######'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'commission_percentage' => fake()->randomElement([10, 15, 20, 25, 30]),
            'receives_transfers_directly' => false,
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withCommission(float $percentage): static
    {
        return $this->state(['commission_percentage' => $percentage]);
    }

    public function receivesTransfers(): static
    {
        return $this->state(['receives_transfers_directly' => true]);
    }
}
