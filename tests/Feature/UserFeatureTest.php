<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'email']);
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    public function test_user_can_be_retrieved()
    {
        $user = User::factory()->create();
        $response = $this->getJson("/api/users/{$user->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $user->id]);
    }

    public function test_user_can_be_updated()
    {
        $user = User::factory()->create();
        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(200)
                 ->assertJson(['name' => 'Updated Name']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_user_can_be_deleted()
    {
        $user = User::factory()->create();
        $response = $this->deleteJson("/api/users/{$user->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
