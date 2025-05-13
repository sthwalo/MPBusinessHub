<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful business registration
     */
    public function test_can_register_business_successfully(): void
    {
        $payload = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/businesses/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user',
                    'business'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'status' => 'pending',
        ]);
    }

    /**
     * Test validation errors during business registration
     */
    public function test_validation_errors_during_registration(): void
    {
        // Missing required fields
        $payload = [
            'businessName' => '',
            'category' => '',
            'district' => '',
            'description' => 'Too short',
            'phone' => 'invalid',
            'email' => 'not-an-email',
            'website' => 'invalid-url',
            'address' => '',
            'password' => 'short',
        ];

        $response = $this->postJson('/api/businesses/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'businessName',
                'category',
                'district',
                'description',
                'phone',
                'email',
                'website',
                'address',
                'password',
            ]);
    }

    /**
     * Test email uniqueness validation
     */
    public function test_email_must_be_unique(): void
    {
        // Create a user with the email we'll try to register
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'existing@example.com', // Already exists
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/businesses/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test category validation
     */
    public function test_category_must_be_valid(): void
    {
        $payload = [
            'businessName' => 'Test Business',
            'category' => 'InvalidCategory', // Not in allowed list
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/businesses/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    /**
     * Test district validation
     */
    public function test_district_must_be_valid(): void
    {
        $payload = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'InvalidDistrict', // Not in allowed list
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/businesses/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['district']);
    }
}
