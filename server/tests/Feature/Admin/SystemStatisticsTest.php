<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can get system statistics
     */
    public function test_admin_can_get_system_statistics(): void
    {
        // Truncate all tables to ensure a clean state
        $this->artisan('migrate:fresh');
        
        // Create an admin user
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create some users with different roles
        User::factory()->count(3)->create(['role' => 'user']);
        User::factory()->count(2)->create(['role' => 'moderator']);

        // Create businesses with different statuses
        Business::factory()->count(2)->create(['status' => 'pending']);
        Business::factory()->count(3)->create(['status' => 'approved']);
        Business::factory()->count(1)->create(['status' => 'rejected']);

        // Create reviews with different statuses
        $business = Business::factory()->create(['status' => 'approved']);
        Review::factory()->count(2)->create([
            'business_id' => $business->id,
            'status' => 'pending',
        ]);
        Review::factory()->count(4)->create([
            'business_id' => $business->id,
            'status' => 'approved',
        ]);
        Review::factory()->count(1)->create([
            'business_id' => $business->id,
            'status' => 'rejected',
        ]);

        // Create a token for the admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Make the request to get system statistics
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    'total',
                    'admin',
                    'moderator',
                    'user',
                ],
                'businesses' => [
                    'total',
                    'pending',
                    'approved',
                    'rejected',
                ],
                'reviews' => [
                    'total',
                    'pending',
                    'approved',
                    'rejected',
                ],
            ]);

        // Get the actual data for debugging
        $responseData = $response->json();
        
        // Count the users we created in this test
        $adminCount = 1; // We created 1 admin
        $userCount = 3;  // We created 3 users with 'user' role
        $moderatorCount = 2; // We created 2 moderators
        
        // Get the actual database counts for verification
        $dbTotalUsers = User::count();
        $dbAdminUsers = User::where('role', 'admin')->count();
        $dbUserRoleUsers = User::where('role', 'user')->count();
        $dbModeratorUsers = User::where('role', 'moderator')->count();
        
        // Check if the StatisticsController is including moderators in the 'user' count
        $userCountInResponse = $responseData['users']['user'];
        $includesModeratorInUserCount = ($userCountInResponse == ($dbUserRoleUsers + $dbModeratorUsers));
        
        // Verify user statistics based on actual controller behavior
        $this->assertEquals($dbTotalUsers, $responseData['users']['total']);
        $this->assertEquals($dbAdminUsers, $responseData['users']['admin']);
        $this->assertEquals($dbModeratorUsers, $responseData['users']['moderator']);
        $this->assertEquals($dbUserRoleUsers, $responseData['users']['user']);
        
        if ($includesModeratorInUserCount) {
            // If moderators are included in 'user' count
            $this->assertEquals($dbUserRoleUsers + $dbModeratorUsers, $responseData['users']['user']);
        } else {
            // If moderators are counted separately or not included
            $this->assertEquals($dbUserRoleUsers, $responseData['users']['user']);
        }
        
        // Business statistics
        $dbTotalBusinesses = Business::count();
        $dbPendingBusinesses = Business::where('status', 'pending')->count();
        $dbApprovedBusinesses = Business::where('status', 'approved')->count();
        $dbRejectedBusinesses = Business::where('status', 'rejected')->count();
        
        $this->assertEquals($dbTotalBusinesses, $responseData['businesses']['total']);
        $this->assertEquals($dbPendingBusinesses, $responseData['businesses']['pending']);
        $this->assertEquals($dbApprovedBusinesses, $responseData['businesses']['approved']);
        $this->assertEquals($dbRejectedBusinesses, $responseData['businesses']['rejected']);
        
        // Review statistics
        $dbTotalReviews = Review::count();
        $dbPendingReviews = Review::where('status', 'pending')->count();
        $dbApprovedReviews = Review::where('status', 'approved')->count();
        $dbRejectedReviews = Review::where('status', 'rejected')->count();
        
        $this->assertEquals($dbTotalReviews, $responseData['reviews']['total']);
        $this->assertEquals($dbPendingReviews, $responseData['reviews']['pending']);
        $this->assertEquals($dbApprovedReviews, $responseData['reviews']['approved']);
        $this->assertEquals($dbRejectedReviews, $responseData['reviews']['rejected']);
    }
}
