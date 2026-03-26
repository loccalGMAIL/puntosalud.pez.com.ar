<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $sessions = fake()->randomElement([5, 8, 10, 12, 20]);

        return [
            'name' => $sessions . ' sesiones',
            'description' => fake()->optional()->sentence(),
            'sessions_included' => $sessions,
            'price' => fake()->randomElement([50000, 80000, 100000, 150000, 200000]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
