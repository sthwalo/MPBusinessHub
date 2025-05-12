<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test email verification with valid verification link
     */
    public function test_can_verify_email_with_valid_link(): void
    {
        // Create an unverified user
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        // Create a valid verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Extract the verification path from the URL
        $verificationPath = parse_url($verificationUrl, PHP_URL_PATH);
        $verificationQuery = parse_url($verificationUrl, PHP_URL_QUERY);
        $fullPath = $verificationPath . '?' . $verificationQuery;
        
        // Make the request to verify
        $response = $this->get($fullPath);

        // Assert the user is now verified
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertJson([
            'success' => true,
            'message' => 'Email has been verified successfully'
        ]);
    }

    /**
     * Test email verification with invalid hash
     */
    public function test_cannot_verify_email_with_invalid_hash(): void
    {
        // Create an unverified user
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        // Create an invalid verification URL with wrong hash
        $invalidVerificationUrl = '/api/email/verify/' . $user->id . '/invalid-hash';
        
        // Make the request to verify
        $response = $this->get($invalidVerificationUrl);

        // Assert the user is still unverified
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid verification link'
        ]);
    }

    /**
     * Test verification of already verified email
     */
    public function test_already_verified_email_returns_appropriate_message(): void
    {
        // Create a verified user
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        // Create a valid verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Extract the verification path from the URL
        $verificationPath = parse_url($verificationUrl, PHP_URL_PATH);
        $verificationQuery = parse_url($verificationUrl, PHP_URL_QUERY);
        $fullPath = $verificationPath . '?' . $verificationQuery;
        
        // Make the request to verify
        $response = $this->get($fullPath);

        // Assert appropriate response
        $response->assertJson([
            'success' => true,
            'message' => 'Email already verified'
        ]);
    }

    /**
     * Test sending verification email
     */
    public function test_can_send_verification_email(): void
    {
        Notification::fake();

        // Create an unverified user
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make a request to send verification email
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification link sent'
            ]);

        Notification::assertSentTo($user, EmailVerificationNotification::class);
    }

    /**
     * Test sending verification email to already verified user
     */
    public function test_cannot_send_verification_email_to_verified_user(): void
    {
        // Create a verified user
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make a request to send verification email
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Email already verified'
            ]);
    }
}
