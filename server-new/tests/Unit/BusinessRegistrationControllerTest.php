<?php

namespace Tests\Unit;

use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Requests\BusinessRegistrationRequest;
use App\Services\BusinessRegistrationService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;
use Exception;

class BusinessRegistrationControllerTest extends TestCase
{
    /**
     * Test successful registration response
     */
    public function test_register_returns_success_response(): void
    {
        // Mock the service
        $mockService = Mockery::mock(BusinessRegistrationService::class);
        
        // Mock the request
        $mockRequest = Mockery::mock(BusinessRegistrationRequest::class);
        
        // Set up the validated data that would be returned from the request
        $validatedData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'businessName' => 'Test Business',
            'description' => 'Test description',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'address' => '123 Test Street',
            'phone' => '+27123456789',
            'website' => 'https://testbusiness.co.za',
        ];
        
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);
        
        // Set up the service response
        $serviceResponse = [
            'token' => 'test-token',
            'user_id' => 1,
            'business_id' => 1,
            'status' => 'pending'
        ];
        
        $mockService->shouldReceive('register')
            ->once()
            ->with($validatedData)
            ->andReturn($serviceResponse);
        
        // Create controller with mocked service
        $controller = new BusinessRegistrationController($mockService);
        
        // Call the register method
        $response = $controller->register($mockRequest);
        
        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        // Convert the response to array to check the structure
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Business registered successfully', $responseData['message']);
        $this->assertEquals($serviceResponse, $responseData['data']);
    }

    /**
     * Test error handling in the controller
     */
    public function test_register_handles_exceptions(): void
    {
        // Mock the service
        $mockService = Mockery::mock(BusinessRegistrationService::class);
        
        // Mock the request
        $mockRequest = Mockery::mock(BusinessRegistrationRequest::class);
        
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([]);
        
        // Make the service throw an exception
        $mockService->shouldReceive('register')
            ->once()
            ->andThrow(new Exception('Registration failed', 409));
        
        // Create controller with mocked service
        $controller = new BusinessRegistrationController($mockService);
        
        // Call the register method
        $response = $controller->register($mockRequest);
        
        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(409, $response->getStatusCode());
        
        // Convert the response to array to check the structure
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Registration failed', $responseData['message']);
    }

    /**
     * Test validation exception handling
     */
    public function test_register_handles_validation_exceptions(): void
    {
        // Mock the service
        $mockService = Mockery::mock(BusinessRegistrationService::class);
        
        // Mock the request
        $mockRequest = Mockery::mock(BusinessRegistrationRequest::class);
        
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([]);
        
        // Make the service throw a validation exception
        $mockService->shouldReceive('register')
            ->once()
            ->andThrow(new Exception('Validation failed', 422));
        
        // Create controller with mocked service
        $controller = new BusinessRegistrationController($mockService);
        
        // Call the register method
        $response = $controller->register($mockRequest);
        
        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        
        // Convert the response to array to check the structure
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
