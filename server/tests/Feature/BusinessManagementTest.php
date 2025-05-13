<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class BusinessManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting business details
     */
    public function test_can_get_business_details(): void
    {
        // Create a user with a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description.',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'status' => 'approved',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request to get business details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/business/details');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $business->id,
                    'name' => 'Test Business',
                    'category' => 'Tourism',
                    'district' => 'Mbombela',
                ]
            ]);
    }

    /**
     * Test getting business details when user has no business
     */
    public function test_get_business_details_returns_error_when_no_business(): void
    {
        // Create a user without a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request to get business details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/business/details');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Business not found'
            ]);
    }

    /**
     * Test updating business profile
     */
    public function test_can_update_business_profile(): void
    {
        // Create a user with a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description.',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'status' => 'approved',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare update data
        $updateData = [
            'name' => 'Updated Business Name',
            'description' => 'This is an updated business description.',
            'phone' => '+27987654321',
            'website' => 'https://updatedbusiness.co.za',
            'address' => '456 Updated Street, Mbombela',
            'social_media' => [
                'facebook' => 'https://facebook.com/updatedbusiness',
                'twitter' => 'https://twitter.com/updatedbusiness',
            ],
            'operating_hours' => [
                'monday' => '09:00 - 17:00',
                'tuesday' => '09:00 - 17:00',
                'wednesday' => '09:00 - 17:00',
                'thursday' => '09:00 - 17:00',
                'friday' => '09:00 - 17:00',
                'saturday' => '10:00 - 14:00',
                'sunday' => 'Closed',
            ],
        ];

        // Make the request to update business profile
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/business/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business profile updated successfully',
            ]);

        // Verify the business was updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Updated Business Name',
            'description' => 'This is an updated business description.',
            'phone' => '+27987654321',
            'website' => 'https://updatedbusiness.co.za',
            'address' => '456 Updated Street, Mbombela',
        ]);
    }

    /**
     * Test updating business profile with invalid data
     */
    public function test_cannot_update_business_profile_with_invalid_data(): void
    {
        // Create a user with a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description.',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'status' => 'approved',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare invalid update data
        $invalidData = [
            'name' => '', // Empty name should fail validation
            'website' => 'not-a-valid-url', // Invalid URL should fail validation
        ];

        // Make the request to update business profile
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/business/update', $invalidData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['name', 'website']);
    }

    /**
     * Test upgrading business plan
     */
    public function test_can_upgrade_business_plan(): void
    {
        $this->markTestSkipped('Skipping plan upgrade test until PaymentService mock is properly configured');
        
        // Create a user with a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'status' => 'approved',
        ]);

        // Create packages
        $basicPackage = Package::create([
            'name' => 'Basic',
            'price_monthly' => 0,
            'price_annual' => 0,
            'is_active' => true,
        ]);
        
        $premiumPackage = Package::create([
            'name' => 'Premium',
            'price_monthly' => 199,
            'price_annual' => 1990,
            'is_active' => true,
        ]);

        // Assign basic package to business
        $business->package_id = $basicPackage->id;
        $business->save();

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare upgrade data
        $upgradeData = [
            'package_id' => $premiumPackage->id,
            'payment_method' => 'credit_card',
            'billing_cycle' => 'monthly',
        ];

        // Mock the payment service response
        $this->mock(\App\Services\PaymentService::class, function ($mock) {
            $mock->shouldReceive('processPackageChange')
                ->once()
                ->andReturn([
                    'success' => true,
                    'redirect_url' => 'https://payment-gateway.com/checkout',
                    'payment_data' => ['transaction_id' => 'test-transaction'],
                    'method' => 'GET',
                ]);
        });

        // Make the request to upgrade business plan
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/packages/upgrade', $upgradeData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'redirect_url' => 'https://payment-gateway.com/checkout',
            ]);
    }

    /**
     * Test business status change to approved
     */
    public function test_admin_can_approve_business(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create a regular user with a pending business
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'status' => 'pending',
        ]);

        // Create a token for the admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Make the request to approve the business
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/businesses/{$business->id}/approve", [
            'reason' => 'Business meets all requirements',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business approved successfully',
            ]);

        // Verify the business status was updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Test business status change to rejected
     */
    public function test_admin_can_reject_business(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create a regular user with a pending business
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'status' => 'pending',
        ]);

        // Create a token for the admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Make the request to reject the business
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/businesses/{$business->id}/reject", [
            'reason' => 'Business information is incomplete',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business rejected successfully',
            ]);

        // Verify the business status was updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'rejected',
            'status_reason' => 'Business information is incomplete',
        ]);
    }

    /**
     * Test non-admin cannot approve business
     */
    public function test_non_admin_cannot_approve_business(): void
    {
        // Create a regular user
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        // Create another user with a pending business
        $businessOwner = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $businessOwner->id,
            'name' => 'Test Business',
            'status' => 'pending',
        ]);

        // Create a token for the regular user
        $token = $user->createToken('user-token')->plainTextToken;

        // Make the request to approve the business
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/businesses/{$business->id}/approve");

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Only administrators can approve businesses.',
            ]);

        // Verify the business status was not updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'pending',
        ]);
    }
}
