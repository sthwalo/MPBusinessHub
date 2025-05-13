<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Notifications\BusinessStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BusinessStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all business statuses
     */
    public function test_can_get_all_business_statuses(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create a token for the admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Make the request to get all statuses
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/business-statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'statuses'
            ])
            ->assertJson([
                'statuses' => ['pending', 'approved', 'rejected']
            ]);
    }

    /**
     * Test updating business status
     */
    public function test_admin_can_update_business_status(): void
    {
        Notification::fake();

        // Create an admin user
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create a regular user with a business
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'status' => 'pending',
        ]);

        // Create a token for the admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Make the request to update the business status
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/businesses/{$business->id}/status", [
            'status' => 'approved',
            'reason' => 'Business meets all requirements',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business status updated successfully',
            ]);

        // Verify the business status was updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved',
            'status_reason' => 'Business meets all requirements',
        ]);

        // Verify notification was sent to the business owner
        Notification::assertSentTo($user, BusinessStatusChanged::class);
    }

    /**
     * Test updating business status with invalid status
     */
    public function test_cannot_update_business_status_with_invalid_status(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create a regular user with a business
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Business',
            'status' => 'pending',
        ]);

        // Create a token for the admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Make the request with an invalid status
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/businesses/{$business->id}/status", [
            'status' => 'invalid-status',
            'reason' => 'Test reason',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Verify the business status was not updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test non-admin cannot update business status
     */
    public function test_non_admin_cannot_update_business_status(): void
    {
        // Create a regular user
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        // Create another user with a business
        $businessOwner = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $businessOwner->id,
            'name' => 'Test Business',
            'status' => 'pending',
        ]);

        // Create a token for the regular user
        $token = $user->createToken('user-token')->plainTextToken;

        // Make the request to update the business status
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/businesses/{$business->id}/status", [
            'status' => 'approved',
            'reason' => 'Business meets all requirements',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Only administrators can update business status.',
            ]);

        // Verify the business status was not updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'pending',
        ]);
    }
}
