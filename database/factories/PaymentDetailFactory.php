<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentDetail>
 */
class PaymentDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'debit_card', 'credit_card', 'qr']),
            'amount' => fake()->randomElement([5000, 8000, 10000, 15000]),
            'received_by' => 'centro',
            'reference' => null,
            'liquidation_id' => null,
            'liquidated_at' => null,
        ];
    }

    public function cash(): static
    {
        return $this->state(['payment_method' => 'cash']);
    }

    public function transfer(): static
    {
        return $this->state(['payment_method' => 'transfer']);
    }

    public function receivedByProfessional(): static
    {
        return $this->state(['received_by' => 'profesional']);
    }
}
