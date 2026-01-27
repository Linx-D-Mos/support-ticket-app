<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fileable_type' => fake()->randomElement([
                'App\Models\Ticket',
                'App\Models\Answer',
            ]),
            'fileable_id' => function(array $attributes){
                return $attributes['fileable_type']::factory()->create()->id;
            },
            'file_path' => fake()->url(),
        ];
    }
}
