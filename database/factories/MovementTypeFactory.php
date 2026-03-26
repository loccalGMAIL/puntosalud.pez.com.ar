<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MovementTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'description' => null,
            'category' => fake()->randomElement(['income', 'expense_detail', 'withdrawal_detail', 'system']),
            'affects_balance' => fake()->randomElement([1, -1, 0]),
            'icon' => '📋',
            'color' => 'gray',
            'is_active' => true,
            'order' => fake()->numberBetween(1, 100),
        ];
    }

    public function income(): static
    {
        return $this->state(['affects_balance' => 1, 'category' => 'income']);
    }

    public function expense(): static
    {
        return $this->state(['affects_balance' => -1, 'category' => 'expense_detail']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withCode(string $code): static
    {
        return $this->state(['code' => $code]);
    }
}
