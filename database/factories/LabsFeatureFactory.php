<?php

namespace Database\Factories;

use App\Domain\Labs\LabsFeatureStatus;
use App\Models\LabsFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabsFeature>
 */
class LabsFeatureFactory extends Factory
{
    protected $model = LabsFeature::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'summary' => fake()->sentence(),
            'why_it_matters' => fake()->paragraph(),
            'status' => LabsFeatureStatus::Planned,
            'notify_enabled' => true,
            'beta_enabled' => true,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
