<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        $dni = fake()->unique()->numerify('########');
        $formatted = strlen($dni) === 7
            ? substr($dni, 0, 1) . '.' . substr($dni, 1, 3) . '.' . substr($dni, 4, 3)
            : substr($dni, 0, 2) . '.' . substr($dni, 2, 3) . '.' . substr($dni, 5, 3);

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'dni' => $formatted,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-5 years')->format('Y-m-d'),
            'address' => fake()->optional(0.6)->address(),
            'health_insurance' => fake()->optional(0.5)->randomElement(['OSDE', 'Swiss Medical', 'Galeno', 'PAMI', 'IOMA']),
            'health_insurance_number' => null,
            'titular_obra_social' => null,
            'plan_obra_social' => null,
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['activo' => false]);
    }

    public function withInsurance(string $insurance = 'OSDE'): static
    {
        return $this->state(['health_insurance' => $insurance]);
    }
}
