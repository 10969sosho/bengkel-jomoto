<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password', 'role' => 'admin',
        ]);

        $response = $this->followingRedirects()->get('/');

        $response->assertStatus(200);
    }
}
