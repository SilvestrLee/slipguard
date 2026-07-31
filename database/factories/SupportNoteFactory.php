<?php

namespace Database\Factories;

use App\Models\SupportNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportNote>
 */
class SupportNoteFactory extends Factory
{
    protected $model = SupportNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory()->operationsStaff(),
            'customer_id' => User::factory(),
            'note' => fake()->sentence(),
        ];
    }
}
