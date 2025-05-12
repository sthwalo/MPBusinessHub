<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sending password reset email
     */
    public function test_can_send_password_reset_email(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson('/api/password/email', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'We have emailed your password reset link.'
            ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /**
     * Test sending password reset email to non-existent user
     */
    public function test_cannot_send_reset_email_to_nonexistent_user(): void
    {
        $response = $this->postJson('/api/password/email', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test resetting password with valid token
     */
    public function test_can_reset_password_with_valid_token(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('old-password'),
        ]);

        // Create a password reset token
        $token = app('auth.password.broker')->createToken($user);

        // Reset the password
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Your password has been reset.'
            ]);

        // Verify the password was updated
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    /**
     * Test resetting password with invalid token
     */
    public function test_cannot_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password validation during reset
     */
    public function test_password_must_meet_requirements_during_reset(): void
    {
        $user = User::factory()->create();
        $token = app('auth.password.broker')->createToken($user);

        // Test with short password
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test with mismatched confirmation
        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'valid-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
