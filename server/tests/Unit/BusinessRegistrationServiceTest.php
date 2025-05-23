<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Business;
use App\Repositories\UserRepository;
use App\Repositories\BusinessRepository;
use App\Services\BusinessRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Exception;
use Mockery;

class BusinessRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepository;
    protected $businessRepository;
    protected $service;

    public function setUp(): void
    {
        parent::setUp();

        // Create real repositories with the models
        $this->userRepository = new UserRepository(new User());
        $this->businessRepository = new BusinessRepository(new Business());

        // Create the service with real repositories
        $this->service = new BusinessRegistrationService(
            $this->userRepository,
            $this->businessRepository
        );
    }

    /**
     * Test successful registration with the service
     */
    public function test_register_creates_user_and_business(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'businessName' => 'Test Business',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'address' => '123 Test Street, Mbombela',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
        ];

        $result = $this->service->register($data);

        // Assert the result structure
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('business', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('id', $result['business']);

        // Assert the user was created
        $user = User::find($result['user']['id']);
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->email);
        
        // Assert password was hashed
        $this->assertTrue(Hash::check('password123', $user->password));

        // Assert the business was created
        $business = Business::find($result['business']['id']);
        $this->assertNotNull($business);
        $this->assertEquals('Test Business', $business->name);
        $this->assertEquals('Tourism', $business->category);
        $this->assertEquals('Mbombela', $business->district);
        $this->assertEquals('pending', $business->status);
        $this->assertEquals($user->id, $business->user_id);
    }

    /**
     * Test that an exception is thrown when email already exists
     */
    public function test_register_throws_exception_when_email_exists(): void
    {
        // Create a user with the email we'll try to register
        User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
        ]);

        $data = [
            'email' => 'existing@example.com', // Already exists
            'password' => 'password123',
            'businessName' => 'Test Business',
            'description' => 'This is a test business description.',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'address' => '123 Test Street, Mbombela',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email already registered');
        $this->expectExceptionCode(409);

        $this->service->register($data);
    }

    /**
     * Test with mocked repositories for more isolated testing
     */
    public function test_register_with_mocked_repositories(): void
    {
        // Create mock repositories
        $userRepository = Mockery::mock(UserRepository::class);
        $businessRepository = Mockery::mock(BusinessRepository::class);

        // Create test data
        $data = [
            'email' => 'mock@example.com',
            'password' => 'password123',
            'businessName' => 'Mock Business',
            'description' => 'This is a mock business description.',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'address' => '123 Mock Street, Mbombela',
            'phone' => '+27123456789',
            'website' => 'https://mockbusiness.co.za',
        ];

        // Set up expectations for the user repository
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('mock@example.com')
            ->andReturn(null);

        $mockUser = Mockery::mock(User::class);
        $mockUser->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $mockUser->shouldReceive('getAttribute')->with('email')->andReturn('mock@example.com');
        $mockUser->shouldReceive('getAttribute')->with('name')->andReturn('Mock Business');
        
        // Allow accessing properties directly
        $mockUser->shouldReceive('__get')->with('id')->andReturn(1);
        $mockUser->shouldReceive('__get')->with('email')->andReturn('mock@example.com');
        $mockUser->shouldReceive('__get')->with('name')->andReturn('Mock Business');
        
        // Add expectation for sendEmailVerificationNotification
        $mockUser->shouldReceive('sendEmailVerificationNotification')
            ->once()
            ->andReturn(null);

        $userRepository->shouldReceive('createUser')
            ->once()
            ->with([
                'name' => 'Mock Business',
                'email' => 'mock@example.com',
                'password' => 'password123',
            ])
            ->andReturn($mockUser);

        // Set up expectations for the business repository
        $mockBusiness = Mockery::mock(Business::class);
        $mockBusiness->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $mockBusiness->shouldReceive('getAttribute')->with('name')->andReturn('Mock Business');
        $mockBusiness->shouldReceive('getAttribute')->with('status')->andReturn('pending');

        $businessRepository->shouldReceive('createBusiness')
            ->once()
            ->with([
                'name' => 'Mock Business',
                'description' => 'This is a mock business description.',
                'category' => 'Tourism',
                'district' => 'Mbombela',
                'address' => '123 Mock Street, Mbombela',
                'phone' => '+27123456789',
                'website' => 'https://mockbusiness.co.za',
            ], 1)
            ->andReturn($mockBusiness);

        // Mock the token creation
        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object)['plainTextToken' => 'mock-token']);

        // Create service with mocked repositories
        $service = new BusinessRegistrationService($userRepository, $businessRepository);

        // Call the method
        $result = $service->register($data);

        // Assert the result
        $this->assertEquals('mock-token', $result['token']);
        $this->assertEquals(1, $result['user']['id']);
        $this->assertEquals(1, $result['business']['id']);
        $this->assertEquals('pending', $result['business']['status']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
