<?php

namespace Database\Factories;

use App\Enums\Type;
use Illuminate\Database\Eloquent\Factories\Factory;
use Termwind\Components\Paragraph;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Label>
 */
class LabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(array_column(Type::cases(), 'value')),
            'description' => fake()->paragraph(),
        ];
    }
}
