<?php

namespace Database\Factories;

use App\Models\Professional;
use App\Models\ProfessionalSlotBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfessionalSlotBlock>
 */
class ProfessionalSlotBlockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'professional_id' => Professional::factory(),
            'block_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'reason' => null,
            'created_by' => null,
        ];
    }
}
