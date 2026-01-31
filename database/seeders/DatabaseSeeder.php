<?php

namespace Database\Seeders;

use App\Models\Label;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(RolSeeder::class);
        $this->call(LabelSeeder::class);
        User::factory(1)->admin()->create();
        User::factory(3)->agent()->create();
        User::factory(6)->customer()->create();
        Ticket::factory(rand(3,8))->urgent()->create();
        Ticket::factory(rand(3,8))->high()->create();
        Ticket::factory(rand(3,8))->medium()->create();
        Ticket::factory(rand(3,8))->low()->create();
        // $customerRolId = Rol::where('name', RolEnum::CUSTOMER->value)->firstOrFail()->id;
        // $agentRolId = Rol::where('name', RolEnum::AGENT->value)->firstOrFail()->id;
        // $customer = User::factory()->create([
        //     'rol_id' => $customerRolId,
        // ]);
        // $agent = User::factory()->create([
        //     'rol_id' => $agentRolId,
        // ]);
        // $ticket = Ticket::factory()->create([
        //     'user_id' => $customer->id,
        // ]);
        // $labels = Label::all();
        // $ticket->update(['agent_id' => $agent->id]);
        // $ticket->labels()->attach($labels->random(rand(1, 3)));
        // $ticket->files()->save(File::make(['file_path' => fake()->url()]));
    }
}
