<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'professional_id' => Professional::factory(),
            'patient_id' => Patient::factory(),
            'office_id' => Office::factory(),
            'appointment_date' => fake()->dateTimeBetween('now', '+30 days'),
            'duration' => fake()->randomElement([30, 45, 60]),
            'status' => 'scheduled',
            'estimated_amount' => fake()->randomElement([5000, 8000, 10000, 15000]),
            'final_amount' => null,
            'notes' => null,
            'created_by' => null,
            'is_between_turn' => false,
        ];
    }

    public function scheduled(): static
    {
        return $this->state([
            'status' => 'scheduled',
            'appointment_date' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    public function attended(float $finalAmount = null): static
    {
        return $this->state(function (array $attributes) use ($finalAmount) {
            return [
                'status' => 'attended',
                'appointment_date' => fake()->dateTimeBetween('-30 days', 'yesterday'),
                'final_amount' => $finalAmount ?? $attributes['estimated_amount'],
            ];
        });
    }

    public function absent(): static
    {
        return $this->state([
            'status' => 'absent',
            'appointment_date' => fake()->dateTimeBetween('-30 days', 'yesterday'),
            'final_amount' => 0,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
        ]);
    }

    public function inPast(): static
    {
        return $this->state([
            'appointment_date' => fake()->dateTimeBetween('-30 days', 'yesterday'),
        ]);
    }

    public function atTime(string $datetime): static
    {
        return $this->state(['appointment_date' => $datetime]);
    }
}
