<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Label;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Rol::where('name', RolEnum::CUSTOMER->value)->value('id') ?? Rol::factory();
        return [
            'user_id' => User::factory()->create(['rol_id' => $customer]),
            'agent_id' => null,
            'title' => fake()->word(),
            'priority' => Priority::LOW->value,
            'status' => Status::OPEN->value,
            'last_reply_at' => null,
        ];
    }
    public function withTickets(): static
    {
        return $this->afterCreating(function (Ticket $ticket) {
            if ($ticket->labels()->count() == 0) {
                $label = Label::inRandomOrder()->first() ?? Label::factory();
                $ticket->labels()->attach($label);
            }
        });
    }
    public function assignedTo(User $agent): static
    {
        return $this->state(fn(array $attribute) => [
            'agent_id' => $agent->id,
            'status' => Status::INPROGRESS,
        ]);
    }
    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => Priority::URGENT,
        ]);
    }
    public function high(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => Priority::HIGH,
        ]);
    }
    public function medium(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => Priority::MEDIUM,
        ]);
    }
    public function low(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => Priority::LOW,
        ]);
    }
}
