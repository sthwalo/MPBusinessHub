<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(new User());
    }

    /**
     * Test creating a user
     */
    public function test_create_user(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->userRepository->createUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotEquals('password123', $user->password); // Password should be hashed
        
        // Verify the user exists in the database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test finding a user by email
     */
    public function test_find_by_email(): void
    {
        // Create a user first
        User::factory()->create([
            'email' => 'find@example.com',
        ]);

        // Find the user by email
        $user = $this->userRepository->findByEmail('find@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('find@example.com', $user->email);
    }

    /**
     * Test finding a user by email returns null when not found
     */
    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $user = $this->userRepository->findByEmail('nonexistent@example.com');

        $this->assertNull($user);
    }

    /**
     * Test finding a user by ID
     */
    public function test_find_by_id(): void
    {
        // Create a user first
        $createdUser = User::factory()->create();

        // Find the user by ID
        $user = $this->userRepository->find($createdUser->id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($createdUser->id, $user->id);
        $this->assertEquals($createdUser->email, $user->email);
    }

    /**
     * Test updating a user
     */
    public function test_update_user(): void
    {
        // Create a user first
        $user = User::factory()->create([
            'email' => 'original@example.com',
        ]);

        // Update the user
        $updatedUser = $this->userRepository->update([
            'email' => 'updated@example.com',
        ], $user->id);

        $this->assertEquals('updated@example.com', $updatedUser->email);
        
        // Verify the user was updated in the database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test deleting a user
     */
    public function test_delete_user(): void
    {
        // Create a user first
        $user = User::factory()->create();

        // Delete the user
        $result = $this->userRepository->delete($user->id);

        $this->assertTrue($result);
        
        // Verify the user was deleted from the database
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
