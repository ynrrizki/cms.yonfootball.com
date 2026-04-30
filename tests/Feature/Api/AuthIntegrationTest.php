<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function spaHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000',
    ];
}

it('logs in with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'auth@example.com',
        'password' => 'password',
    ]);

    $response = $this
        ->withHeaders(spaHeaders())
        ->postJson('/api/auth/login', [
            'email' => 'auth@example.com',
            'password' => 'password',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email);

    expect(auth()->check())->toBeTrue();
});

it('rejects invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'auth@example.com',
        'password' => 'password',
    ]);

    $response = $this
        ->withHeaders(spaHeaders())
        ->postJson('/api/auth/login', [
            'email' => 'auth@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('returns current user for authenticated requests', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withHeaders(spaHeaders())
        ->getJson('/api/auth/me');

    $response
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email);
});

it('logs out authenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withHeaders(spaHeaders())
        ->postJson('/api/auth/logout');

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');
});
