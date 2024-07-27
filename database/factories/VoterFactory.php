<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Faker\UruguayanIdProvider;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class VoterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->addProvider(new UruguayanIdProvider($this->faker));
        
        return [
            'name' => fake()->name(),
            'lastName' => fake()->lastName(),
            'document' => $this->faker->uruguayanId(),
            'dob' => $this->faker->date(),
            'is_candidate' => $this->faker->boolean(),
        ];
    }
}
