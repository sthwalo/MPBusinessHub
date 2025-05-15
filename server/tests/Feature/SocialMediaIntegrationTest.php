<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SocialMediaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test updating social media links
     */
    public function test_can_update_social_media_links(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Bronze package (minimum for social media links)
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver', // Using Silver since Bronze isn't a valid package type
            'social_media' => null,
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare social media data
        $socialMediaData = [
            'facebook' => 'https://facebook.com/testbusiness',
            'instagram' => 'https://instagram.com/testbusiness',
            'twitter' => 'https://twitter.com/testbusiness',
            'linkedin' => 'https://linkedin.com/company/testbusiness',
            'youtube' => 'https://youtube.com/channel/testbusiness',
            'tiktok' => 'https://tiktok.com/@testbusiness',
        ];

        // Make the request to update social media links
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/business/social-media', $socialMediaData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Social media links updated successfully',
                'data' => $socialMediaData,
            ]);

        // Refresh the business from the database
        $business->refresh();

        // Assert the social media links were updated
        $this->assertEquals($socialMediaData, $business->social_media);
    }

    /**
     * Test updating social media links validation
     */
    public function test_social_media_links_validation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare invalid social media data
        $invalidData = [
            'facebook' => 'not-a-url',
            'instagram' => 'also-not-a-url',
        ];

        // Make the request with invalid data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/business/social-media', $invalidData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['facebook', 'instagram']);
    }

    /**
     * Test social media links for non-existent business
     */
    public function test_social_media_links_no_business(): void
    {
        // Create a user without a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare social media data
        $socialMediaData = [
            'facebook' => 'https://facebook.com/testbusiness',
        ];

        // Make the request to update social media links
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/business/social-media', $socialMediaData);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Business not found',
            ]);
    }

    /**
     * Test package restriction for social media links
     */
    public function test_package_restriction_for_social_media_links(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Free package (doesn't allow social media)
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Free', // Free package doesn't include social media
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare social media data
        $socialMediaData = [
            'facebook' => 'https://facebook.com/testbusiness',
        ];

        // Make the request to update social media links
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/business/social-media', $socialMediaData);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your current package does not include social media links. Please upgrade to Bronze or higher.',
            ]);
    }

    /**
     * Test creating a social media feature post
     */
    public function test_can_create_feature_post(): void
    {
        // Create fake storage disk for image uploads
        Storage::fake('public');

        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Silver package
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver',
            'social_features_remaining' => 3, // Ensure there are remaining features
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Create a fake image
        $image = UploadedFile::fake()->image('feature-post.jpg');

        // Prepare feature post data
        $postData = [
            'platform' => 'facebook',
            'content' => 'This is a test feature post for our business!',
            'image' => $image,
        ];

        // Make the request to create a feature post
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/business/social-media/feature', $postData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Social media feature post created successfully',
            ]);

        // Refresh the business from the database
        $business->refresh();

        // Assert the social features remaining was decremented
        $this->assertEquals(2, $business->social_features_remaining);
    }

    /**
     * Test feature post validation
     */
    public function test_feature_post_validation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver',
            'social_features_remaining' => 3,
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare invalid feature post data (missing required fields)
        $invalidData = [
            'platform' => 'invalid-platform', // Not in the allowed list
        ];

        // Make the request with invalid data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/business/social-media/feature', $invalidData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['platform', 'content']);
    }

    /**
     * Test package restriction for feature posts
     */
    public function test_package_restriction_for_feature_posts(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Free package (doesn't allow feature posts)
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Free', // Free package doesn't include feature posts
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare feature post data
        $postData = [
            'platform' => 'facebook',
            'content' => 'This is a test feature post for our business!',
        ];

        // Make the request to create a feature post
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/business/social-media/feature', $postData);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'This feature is only available for Silver and Gold package subscribers',
            ]);
    }

    /**
     * Test feature post limit reached
     */
    public function test_feature_post_limit_reached(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Silver package but no remaining features
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver',
            'social_features_remaining' => 0, // No remaining features
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare feature post data
        $postData = [
            'platform' => 'facebook',
            'content' => 'This is a test feature post for our business!',
        ];

        // Make the request to create a feature post
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/business/social-media/feature', $postData);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You have used all your social media features for this month',
            ]);
    }
}
