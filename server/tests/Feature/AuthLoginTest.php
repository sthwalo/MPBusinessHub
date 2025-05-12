<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        // Create a verified user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'email' => 'test@example.com',
                        'email_verified' => true
                    ]
                ]
            ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with unverified email
     */
    public function test_user_cannot_login_with_unverified_email(): void
    {
        // Create an unverified user
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $payload = [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Email not verified. Please check your email for verification link.',
                'email_verified' => false
            ]);
    }

    /**
     * Test account locking after too many failed attempts
     */
    public function test_account_locks_after_too_many_failed_attempts(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Attempt to login with wrong password 5 times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Refresh user from database
        $user->refresh();
        
        // Verify the account is now locked
        $this->assertNotNull($user->locked_until);
        
        // Try to login with correct password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message',
                'locked_until'
            ])
            ->assertJson([
                'success' => false
            ]);
    }

    /**
     * Test reset of failed login attempts after successful login
     */
    public function test_failed_login_attempts_reset_after_successful_login(): void
    {
        // Create a user with some failed login attempts
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'failed_login_attempts' => 3,
            'locked_until' => null,
        ]);

        // Login successfully
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        // Refresh user from database
        $user->refresh();
        
        // Verify failed login attempts were reset
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }
}
