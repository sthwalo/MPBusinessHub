<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing active sessions
     */
    public function test_can_list_active_sessions(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create multiple tokens for the user
        $token1 = $user->createToken('iPhone Device')->plainTextToken;
        $token2 = $user->createToken('MacBook Pro')->plainTextToken;
        $token3 = $user->createToken('iPad')->plainTextToken;

        // Make the request with one of the tokens
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->getJson('/api/sessions');

        $response->assertStatus(200);
        
        // Check that the response contains all three sessions
        $sessions = $response->json('sessions');
        $this->assertCount(3, $sessions);
        
        // Verify the device names are present
        $deviceNames = collect($sessions)->pluck('device')->toArray();
        $this->assertContains('iPhone Device', $deviceNames);
        $this->assertContains('MacBook Pro', $deviceNames);
        $this->assertContains('iPad', $deviceNames);
        
        // Verify the current device is marked correctly
        $currentSession = collect($sessions)->firstWhere('device', 'iPhone Device');
        $this->assertTrue($currentSession['is_current_device']);
    }

    /**
     * Test destroying a specific session
     */
    public function test_can_destroy_specific_session(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create multiple tokens for the user
        $token1 = $user->createToken('Current Device')->plainTextToken;
        $token2 = $user->createToken('Other Device')->plainTextToken;
        
        // Get the token ID for the second token
        $tokenId = explode('|', $token2)[0];
        
        // Make the request to destroy the second token using the first token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->deleteJson("/api/sessions/{$tokenId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Session revoked successfully',
            ]);
            
        // Verify the token was actually deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
        
        // Verify the current token still exists
        $currentTokenId = explode('|', $token1)[0];
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentTokenId,
        ]);
    }

    /**
     * Test attempting to destroy the current session
     */
    public function test_cannot_destroy_current_session(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('Current Device')->plainTextToken;
        $tokenId = explode('|', $token)[0];
        
        // Attempt to destroy the current token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/sessions/{$tokenId}");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot revoke the current session',
            ]);
            
        // Verify the token still exists
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    /**
     * Test destroying all sessions except the current one
     */
    public function test_can_destroy_all_other_sessions(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create multiple tokens for the user
        $token1 = $user->createToken('Current Device')->plainTextToken;
        $user->createToken('Device 2')->plainTextToken;
        $user->createToken('Device 3')->plainTextToken;
        
        // Get the current token ID
        $currentTokenId = explode('|', $token1)[0];
        
        // Make the request to destroy all other sessions
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->deleteJson('/api/sessions');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All other sessions revoked successfully',
            ]);
            
        // Verify only the current token exists
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentTokenId,
        ]);
    }

    /**
     * Test updating activity for a session
     */
    public function test_can_update_session_activity(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('Test Device')->plainTextToken;
        $tokenId = explode('|', $token)[0];
        
        // Make the request to update activity
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/sessions/activity');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Activity updated',
            ]);
            
        // Verify the last_used_at field was updated
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
            'last_used_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Test unauthorized access to sessions
     */
    public function test_unauthenticated_user_cannot_access_sessions(): void
    {
        // Attempt to access sessions without authentication
        $response = $this->getJson('/api/sessions');

        $response->assertStatus(401);
    }
}
