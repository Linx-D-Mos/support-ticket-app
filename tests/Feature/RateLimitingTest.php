<?php

use App\Enums\Priority;
use App\Models\User;

test('Can protect the app from a script', function () {
    $user = User::factory()->customer()->create();
    for ($i = 1; $i <= 100; $i++) {

        if ($i <= 60) {
            $request = $this->actingAs($user)
                ->postJson('api/tickets', [
                    'title' => 'Error',
                    'priority' => Priority::HIGH->value,
                    'labels' => ['incident'],
                ])->assertStatus(201);
        } else {
            $this->actingAs($user)
                ->postJson('api/tickets', [
                    'title' => 'Error',
                    'priority' => Priority::HIGH->value,
                    'labels' => ['incident'],
                ])->assertStatus(429);
        }
    }
});
