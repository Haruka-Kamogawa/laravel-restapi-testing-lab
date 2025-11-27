<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthAndTodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_returns_token()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->postJson('/api/register', $payload)
            ->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->postJson('/api/login', ['email' => 'test@example.com', 'password' => 'password'])
            ->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_authenticated_user_can_crud_todos()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // create
        $create = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/todos', ['title' => 'My first todo']);
        $create->assertStatus(201)
            ->assertJsonFragment(['title' => 'My first todo']);
            
        $id = $create->json('data.id');

        // index
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/todos')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // show
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/todos/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id);

        //update
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/todos/{$id}", ['title' => 'Updated', 'is_done' => true])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated', 'is_done' => true]);

        // delete
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/todos/{$id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);

        $this->assertDatabaseMissing('todos', ['id' => $id]);
    }

    public function test_cannot_access_others_todo()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $todo = Todo::factory()->for($user1)->create();

        $token2 = $user2->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson("/api/todos/{$todo->id}")
            ->assertStatus(403);
    }
}
