<?php

namespace Database\Seeders;

use App\Enums\RolEnum;
use App\Enums\Type;
use App\Models\File;
use App\Models\Label;
use App\Models\Rol;
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

        $this->seed(LabelSeeder::class);
        $this->seed(RolSeeder::class);
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
