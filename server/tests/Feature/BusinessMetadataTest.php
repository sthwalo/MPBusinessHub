<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BusinessMetadataTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test getting categories returns correct format
     *
     * @return void
     */
    public function test_get_categories_returns_correct_format()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create some businesses with different categories
        $categories = ['Tourism', 'Agriculture', 'Construction', 'Events'];
        foreach ($categories as $category) {
            Business::factory()->create([
                'user_id' => $user->id,
                'category' => $category,
                'status' => 'approved'
            ]);
        }
        
        // Make the API request
        $response = $this->getJson('/api/categories');
        
        // Assert response structure
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => $categories
                ]);
    }
    
    /**
     * Test getting districts returns correct format
     *
     * @return void
     */
    public function test_get_districts_returns_correct_format()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create some businesses with different districts
        $districts = ['Mbombela', 'Emalahleni', 'Bushbuckridge'];
        foreach ($districts as $district) {
            Business::factory()->create([
                'user_id' => $user->id,
                'district' => $district,
                'status' => 'approved'
            ]);
        }
        
        // Make the API request
        $response = $this->getJson('/api/districts');
        
        // Assert response structure
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => $districts
                ]);
    }
    
    /**
     * Test categories endpoint only returns categories from approved businesses
     *
     * @return void
     */
    public function test_categories_only_returns_from_approved_businesses()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create approved businesses
        $approvedCategories = ['Tourism', 'Agriculture'];
        foreach ($approvedCategories as $category) {
            Business::factory()->create([
                'user_id' => $user->id,
                'category' => $category,
                'status' => 'approved'
            ]);
        }
        
        // Create pending/rejected businesses
        $pendingCategories = ['Construction', 'Events'];
        $statuses = ['pending', 'rejected'];
        foreach ($pendingCategories as $index => $category) {
            Business::factory()->create([
                'user_id' => $user->id,
                'category' => $category,
                'status' => $statuses[$index % count($statuses)]
            ]);
        }
        
        // Make the API request
        $response = $this->getJson('/api/categories');
        
        // Assert response only includes approved categories
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success'
                ]);
                
        $responseData = $response->json('data');
        
        // Check that only approved categories are returned
        foreach ($approvedCategories as $category) {
            $this->assertContains($category, $responseData);
        }
        
        // Check that pending/rejected categories are not returned
        foreach ($pendingCategories as $category) {
            $this->assertNotContains($category, $responseData);
        }
    }
    
    /**
     * Test districts endpoint only returns districts from approved businesses
     *
     * @return void
     */
    public function test_districts_only_returns_from_approved_businesses()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create approved businesses
        $approvedDistricts = ['Mbombela', 'Emalahleni'];
        foreach ($approvedDistricts as $district) {
            Business::factory()->create([
                'user_id' => $user->id,
                'district' => $district,
                'status' => 'approved'
            ]);
        }
        
        // Create pending/rejected businesses
        $pendingDistricts = ['Bushbuckridge', 'Nelspruit'];
        $statuses = ['pending', 'rejected'];
        foreach ($pendingDistricts as $index => $district) {
            Business::factory()->create([
                'user_id' => $user->id,
                'district' => $district,
                'status' => $statuses[$index % count($statuses)]
            ]);
        }
        
        // Make the API request
        $response = $this->getJson('/api/districts');
        
        // Assert response only includes approved districts
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success'
                ]);
                
        $responseData = $response->json('data');
        
        // Check that only approved districts are returned
        foreach ($approvedDistricts as $district) {
            $this->assertContains($district, $responseData);
        }
        
        // Check that pending/rejected districts are not returned
        foreach ($pendingDistricts as $district) {
            $this->assertNotContains($district, $responseData);
        }
    }
    
    /**
     * Test error handling when database query fails
     *
     * @return void
     */
    public function test_error_handling_when_query_fails()
    {
        // Mock the BusinessSearchService to throw an exception
        $this->mock(\App\Services\BusinessSearchService::class, function ($mock) {
            $mock->shouldReceive('getCategories')
                ->once()
                ->andThrow(new \Exception('Database error'));
                
            $mock->shouldReceive('getDistricts')
                ->once()
                ->andThrow(new \Exception('Database error'));
        });
        
        // Test categories endpoint
        $categoriesResponse = $this->getJson('/api/categories');
        $categoriesResponse->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'An error occurred while retrieving categories: Database error'
                ]);
                
        // Test districts endpoint
        $districtsResponse = $this->getJson('/api/districts');
        $districtsResponse->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'An error occurred while retrieving districts: Database error'
                ]);
    }
}
