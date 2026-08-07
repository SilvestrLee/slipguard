<?php

namespace Database\Factories;

use App\Domain\Contact\ContactMessageCategory;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'subject' => $this->faker->sentence(4),
            'category' => ContactMessageCategory::GeneralEnquiry,
            'message' => $this->faker->paragraph(),
        ];
    }
}
