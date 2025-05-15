<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTrackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test view count incrementation
     */
    public function test_can_increment_view_count(): void
    {
        // Create a business
        $business = Business::factory()->create([
            'view_count' => 5,
        ]);

        // Make the request to increment view count
        $response = $this->postJson("/api/businesses/{$business->id}/view");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'View count incremented successfully',
                'view_count' => 6,
            ]);

        // Verify the view count was incremented in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'view_count' => 6,
        ]);
    }

    /**
     * Test contact count incrementation
     */
    public function test_can_increment_contact_count(): void
    {
        // Create a business
        $business = Business::factory()->create([
            'contact_count' => 10,
        ]);

        // Make the request to increment contact count
        $response = $this->postJson("/api/businesses/{$business->id}/contact");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Contact count incremented successfully',
                'contact_count' => 11,
            ]);

        // Verify the contact count was incremented in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'contact_count' => 11,
        ]);
    }

    /**
     * Test view count incrementation for non-existent business
     */
    public function test_view_count_non_existent_business(): void
    {
        // Make the request with a non-existent business ID
        $response = $this->postJson("/api/businesses/9999/view");

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Failed to increment view count: No query results for model [App\\Models\\Business] 9999',
            ]);
    }

    /**
     * Test contact count incrementation for non-existent business
     */
    public function test_contact_count_non_existent_business(): void
    {
        // Make the request with a non-existent business ID
        $response = $this->postJson("/api/businesses/9999/contact");

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Failed to increment contact count: No query results for model [App\\Models\\Business] 9999',
            ]);
    }

    /**
     * Test multiple view count increments
     */
    public function test_multiple_view_count_increments(): void
    {
        // Create a business with zero view count
        $business = Business::factory()->create([
            'view_count' => 0,
        ]);

        // Make multiple requests to increment view count
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson("/api/businesses/{$business->id}/view");

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'View count incremented successfully',
                    'view_count' => $i,
                ]);
        }

        // Verify the final view count in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'view_count' => 5,
        ]);
    }

    /**
     * Test multiple contact count increments
     */
    public function test_multiple_contact_count_increments(): void
    {
        // Create a business with zero contact count
        $business = Business::factory()->create([
            'contact_count' => 0,
        ]);

        // Make multiple requests to increment contact count
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->postJson("/api/businesses/{$business->id}/contact");

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Contact count incremented successfully',
                    'contact_count' => $i,
                ]);
        }

        // Verify the final contact count in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'contact_count' => 3,
        ]);
    }
}
