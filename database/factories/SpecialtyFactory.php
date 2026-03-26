<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Specialty>
 */
class SpecialtyFactory extends Factory
{
    public function definition(): array
    {
        $specialties = [
            'Clínica Médica', 'Kinesiología', 'Fonoaudiología', 'Psicología',
            'Nutrición', 'Cardiología', 'Neurología', 'Traumatología',
        ];

        return [
            'name' => fake()->unique()->randomElement($specialties),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
