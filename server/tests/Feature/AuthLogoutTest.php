<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful logout
     */
    public function test_user_can_logout_successfully(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make a request with the token to logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        // Verify that the token has been deleted
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * Test logout without authentication
     */
    public function test_unauthenticated_logout_returns_error(): void
    {
        // Attempt to logout without a token
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test that all tokens are revoked on logout
     */
    public function test_all_tokens_are_revoked_on_logout(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Create multiple tokens for the user
        $token1 = $user->createToken('device-1')->plainTextToken;
        $token2 = $user->createToken('device-2')->plainTextToken;
        $token3 = $user->createToken('device-3')->plainTextToken;

        // Verify that we have 3 tokens in the database
        $this->assertDatabaseCount('personal_access_tokens', 3);

        // Make a request with one of the tokens to logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);

        // Verify that all tokens have been deleted
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
