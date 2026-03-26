<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentAppointment>
 */
class PaymentAppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'appointment_id' => Appointment::factory()->attended(),
            'professional_id' => Professional::factory(),
            'allocated_amount' => fake()->randomElement([5000, 8000, 10000]),
            'is_liquidation_trigger' => true,
        ];
    }

    public function notLiquidationTrigger(): static
    {
        return $this->state(['is_liquidation_trigger' => false]);
    }
}
