<?php

namespace Database\Factories;

use App\Domain\Journal\JournalEntryCategory;
use App\Models\JournalEntry;
use App\Models\SlipAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slip_analysis_id' => SlipAnalysis::factory(),
            'user_id' => fn (array $attributes) => SlipAnalysis::find($attributes['slip_analysis_id'])->user_id,
            'analysis_was_linked' => true,
            'title' => fake()->optional()->sentence(4),
            'category' => fake()->randomElement(JournalEntryCategory::cases()),
            'reflection' => fake()->sentence(),
            'next_time_note' => fake()->optional()->sentence(),
        ];
    }
}
