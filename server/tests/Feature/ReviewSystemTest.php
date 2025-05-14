<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving all approved reviews
     */
    public function test_can_get_all_approved_reviews(): void
    {
        // Clear any existing reviews to ensure clean test state
        Review::query()->delete();
        
        // Create a business
        $business = Business::factory()->create([
            'status' => 'approved',
        ]);

        // Create some approved reviews
        Review::factory()->count(3)->create([
            'business_id' => $business->id,
            'status' => 'approved',
        ]);

        // Create some pending reviews that shouldn't be returned
        Review::factory()->count(2)->create([
            'business_id' => $business->id,
            'status' => 'pending',
        ]);

        // Make the request to get all approved reviews
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'business_id',
                        'rating',
                        'comment',
                        'reviewer_name',
                        'status',
                        'created_at',
                    ]
                ]
            ]);

        // Verify that only approved reviews are returned
        $this->assertCount(3, $response->json('data'));
        
    }

    /**
     * Test retrieving reviews for a specific business
     */
    public function test_can_get_business_reviews(): void
    {
        // Create a business
        $business = Business::factory()->create([
            'status' => 'approved',
            'name' => 'Test Business',
        ]);

        // Create some approved reviews for this business
        Review::factory()->count(3)->create([
            'business_id' => $business->id,
            'status' => 'approved',
        ]);

        // Create some pending reviews that shouldn't be returned
        Review::factory()->count(2)->create([
            'business_id' => $business->id,
            'status' => 'pending',
        ]);

        // Make the request to get business reviews
        $response = $this->getJson("/api/businesses/{$business->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'business_id',
                    'business_name',
                    'average_rating',
                    'review_count',
                    'reviews' => [
                        '*' => [
                            'id',
                            'rating',
                            'comment',
                            'reviewer_name',
                            'created_at',
                        ]
                    ]
                ]
            ]);

        // Verify that only approved reviews are returned
        $this->assertEquals(3, count($response->json('data.reviews')));
        $this->assertEquals('Test Business', $response->json('data.business_name'));
    }

    /**
     * Test submitting a review as an authenticated user
     */
    public function test_authenticated_user_can_submit_review(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business
        $business = Business::factory()->create([
            'status' => 'approved',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare review data
        $reviewData = [
            'business_id' => $business->id,
            'rating' => 4,
            'comment' => 'This is a test review from an authenticated user.',
            'reviewer_name' => 'Test User',
            'reviewer_email' => 'test@example.com',
        ];

        // Make the request to submit a review
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Review submitted successfully',
            ]);

        // Verify the review was created in the database
        $this->assertDatabaseHas('reviews', [
            'business_id' => $business->id,
            'rating' => 4,
            'comment' => 'This is a test review from an authenticated user.',
            'reviewer_name' => 'Test User',
            'reviewer_email' => 'test@example.com',
            'status' => 'approved', // Authenticated user reviews are auto-approved
        ]);
    }

    /**
     * Test submitting an anonymous review
     */
    public function test_can_submit_anonymous_review(): void
    {
        // Create a business
        $business = Business::factory()->create([
            'status' => 'approved',
        ]);

        // Prepare review data
        $reviewData = [
            'business_id' => $business->id,
            'rating' => 3,
            'comment' => 'This is a test anonymous review.',
            'reviewer_name' => 'Anonymous User',
            'reviewer_email' => 'anonymous@example.com',
        ];

        // Make the request to submit an anonymous review
        $response = $this->postJson('/api/reviews/anonymous', $reviewData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Thank you for your review. It has been submitted for approval.',
            ]);

        // Verify the review was created in the database with pending status
        $this->assertDatabaseHas('reviews', [
            'business_id' => $business->id,
            'rating' => 3,
            'comment' => 'This is a test anonymous review.',
            'reviewer_name' => 'Anonymous User',
            'reviewer_email' => 'anonymous@example.com',
            'status' => 'pending', // Anonymous reviews require approval
        ]);
    }

    /**
     * Test validation when submitting a review
     */
    public function test_review_submission_validation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare invalid review data
        $invalidData = [
            'business_id' => 999, // Non-existent business ID
            'rating' => 6, // Rating out of range
            'reviewer_name' => '', // Empty name
            'reviewer_email' => 'invalid-email', // Invalid email
        ];

        // Make the request to submit a review
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['business_id', 'rating', 'reviewer_name', 'reviewer_email']);
    }

    /**
     * Test retrieving reviews for a non-existent business
     */
    public function test_get_reviews_for_nonexistent_business(): void
    {
        // Make the request to get reviews for a non-existent business
        // Use a very large ID to ensure it doesn't exist
        $nonExistentId = 9999999;
        
        // Ensure the business doesn't exist
        $this->assertDatabaseMissing('businesses', ['id' => $nonExistentId]);
        
        $response = $this->getJson("/api/businesses/{$nonExistentId}/reviews");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Business not found'
            ]);
    }
}
