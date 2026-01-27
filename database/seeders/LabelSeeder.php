<?php

namespace Database\Seeders;

use App\Enums\Type;
use App\Models\Label;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Type::cases() as $type) {
        Label::firstOrCreate([
                'name' => $type->value
            ], [
                'description' => fake()->paragraph()
            ]);
        }
    }
}
