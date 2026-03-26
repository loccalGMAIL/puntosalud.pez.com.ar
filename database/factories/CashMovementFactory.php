<?php

namespace Database\Factories;

use App\Models\MovementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'movement_type_id' => MovementType::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'description' => fake()->sentence(),
            'reference_type' => null,
            'reference_id' => null,
            'balance_after' => fake()->randomFloat(2, 0, 100000),
            'user_id' => User::factory(),
        ];
    }

    public function income(): static
    {
        return $this->state([
            'movement_type_id' => MovementType::factory()->income(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
        ]);
    }

    public function expense(): static
    {
        return $this->state([
            'movement_type_id' => MovementType::factory()->expense(),
            'amount' => -fake()->randomFloat(2, 500, 20000),
        ]);
    }

    public function withBalance(float $balance): static
    {
        return $this->state(['balance_after' => $balance]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(['amount' => $amount]);
    }
}
