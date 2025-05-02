<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Business;
use App\Repositories\BusinessRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $businessRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->businessRepository = new BusinessRepository(new Business());
    }

    /**
     * Test creating a business
     */
    public function test_create_business(): void
    {
        // Create a user first
        $user = User::factory()->create();

        $businessData = [
            'name' => 'Test Business',
            'description' => 'This is a test business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'address' => '123 Test Street',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
        ];

        $business = $this->businessRepository->createBusiness($businessData, $user->id);

        $this->assertInstanceOf(Business::class, $business);
        $this->assertEquals('Test Business', $business->name);
        $this->assertEquals('Tourism', $business->category);
        $this->assertEquals('Mbombela', $business->district);
        $this->assertEquals($user->id, $business->user_id);
        $this->assertEquals('pending', $business->status); // Default status
        
        // Verify the business exists in the database
        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Business',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test finding a business by user ID
     */
    public function test_find_by_user_id(): void
    {
        // Create a user first
        $user = User::factory()->create();

        // Create a business for the user
        Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'User Business',
        ]);

        // Find the business by user ID
        $business = $this->businessRepository->findByUserId($user->id);

        $this->assertInstanceOf(Business::class, $business);
        $this->assertEquals('User Business', $business->name);
        $this->assertEquals($user->id, $business->user_id);
    }

    /**
     * Test finding a business by user ID returns null when not found
     */
    public function test_find_by_user_id_returns_null_when_not_found(): void
    {
        $business = $this->businessRepository->findByUserId(999); // Non-existent user ID

        $this->assertNull($business);
    }

    /**
     * Test finding a business by ID
     */
    public function test_find_by_id(): void
    {
        // Create a user first
        $user = User::factory()->create();

        // Create a business
        $createdBusiness = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Find Business',
        ]);

        // Find the business by ID
        $business = $this->businessRepository->find($createdBusiness->id);

        $this->assertInstanceOf(Business::class, $business);
        $this->assertEquals($createdBusiness->id, $business->id);
        $this->assertEquals('Find Business', $business->name);
        $this->assertEquals($user->id, $business->user_id);
    }

    /**
     * Test updating a business
     */
    public function test_update_business(): void
    {
        // Create a user first
        $user = User::factory()->create();

        // Create a business
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Business',
            'category' => 'Tourism',
        ]);

        // Update the business
        $updatedBusiness = $this->businessRepository->update([
            'name' => 'Updated Business',
            'category' => 'Agriculture',
        ], $business->id);

        $this->assertEquals('Updated Business', $updatedBusiness->name);
        $this->assertEquals('Agriculture', $updatedBusiness->category);
        
        // Verify the business was updated in the database
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Updated Business',
            'category' => 'Agriculture',
        ]);
    }

    /**
     * Test deleting a business
     */
    public function test_delete_business(): void
    {
        // Create a user first
        $user = User::factory()->create();

        // Create a business
        $business = Business::factory()->create([
            'user_id' => $user->id,
        ]);

        // Delete the business
        $result = $this->businessRepository->delete($business->id);

        $this->assertTrue($result);
        
        // Verify the business was deleted from the database
        $this->assertDatabaseMissing('businesses', [
            'id' => $business->id,
        ]);
    }
}
