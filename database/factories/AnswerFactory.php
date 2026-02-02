<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::inRandomOrder()->first() ?? Ticket::factory()->create(),
            'user_id' => User::inRandomOrder()->first() ?? User::factory()->create(),
            'body' => fake()->paragraph(),
        ];
    }
    public function assignedTo(Ticket $ticket): static
    {
        return $this->state(fn(array $attribute) => [
            'ticket_id' => $ticket->id,
        ]);
    }
    public function createBy(User $user): static
    {
        return $this->state(fn(array $attribute) => [
            'user_id' => $user->id,
        ]);
    }
}
