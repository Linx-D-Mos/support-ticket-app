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
        User::factory(1)->admin()->create(['email' => 'admin@test.com']);
        User::factory(1)->agent()->create(['email' => 'agent@test.com']);
        User::factory(1)->agent()->create(['email' => 'agent2@test.com']);
        User::factory(1)->customer()->create(['email' => 'customer@test.com']);
        User::factory(1)->customer()->create(['email' => 'customer2@test.com']);
    }
}
