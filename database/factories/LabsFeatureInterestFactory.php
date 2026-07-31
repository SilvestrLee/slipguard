<?php

namespace Database\Factories;

use App\Domain\Labs\LabsInterestType;
use App\Models\LabsFeature;
use App\Models\LabsFeatureInterest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabsFeatureInterest>
 */
class LabsFeatureInterestFactory extends Factory
{
    protected $model = LabsFeatureInterest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'labs_feature_id' => LabsFeature::factory(),
            'type' => LabsInterestType::Notify,
        ];
    }
}
