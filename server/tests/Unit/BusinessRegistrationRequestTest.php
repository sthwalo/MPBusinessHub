<?php

namespace Tests\Unit;

use App\Http\Requests\BusinessRegistrationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BusinessRegistrationRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $request;

    public function setUp(): void
    {
        parent::setUp();
        $this->request = new BusinessRegistrationRequest();
    }

    /**
     * Test validation passes with valid data
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $validData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($validData, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test business name validation
     */
    public function test_business_name_validation(): void
    {
        $invalidData = [
            'businessName' => '', // Empty business name
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('businessName', $validator->errors()->toArray());
    }

    /**
     * Test category validation
     */
    public function test_category_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'InvalidCategory', // Invalid category
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());
    }

    /**
     * Test district validation
     */
    public function test_district_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'InvalidDistrict', // Invalid district
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('district', $validator->errors()->toArray());
    }

    /**
     * Test description validation
     */
    public function test_description_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'Too short', // Description too short
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /**
     * Test phone validation
     */
    public function test_phone_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => 'invalid-phone', // Invalid phone format
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    /**
     * Test email validation
     */
    public function test_email_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'invalid-email', // Invalid email format
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Test website validation
     */
    public function test_website_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'invalid-website', // Invalid website format
            'address' => '123 Test Street, Mbombela',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('website', $validator->errors()->toArray());
    }

    /**
     * Test password validation
     */
    public function test_password_validation(): void
    {
        $invalidData = [
            'businessName' => 'Test Business',
            'category' => 'Tourism',
            'district' => 'Mbombela',
            'description' => 'This is a test business description with more than fifty characters to pass validation.',
            'phone' => '+27123456789',
            'email' => 'test@example.com',
            'website' => 'https://testbusiness.co.za',
            'address' => '123 Test Street, Mbombela',
            'password' => '123', // Password too short
        ];

        $validator = Validator::make($invalidData, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * Test custom error messages
     */
    public function test_custom_error_messages(): void
    {
        $invalidData = [
            'businessName' => '',
            'category' => '',
            'district' => '',
            'description' => 'Too short',
            'phone' => 'invalid',
            'email' => 'invalid',
            'website' => 'invalid',
            'address' => '',
            'password' => '123',
        ];

        $validator = Validator::make(
            $invalidData, 
            $this->request->rules(), 
            $this->request->messages()
        );

        $errors = $validator->errors()->toArray();

        $this->assertEquals('Business name is required', $errors['businessName'][0]);
        $this->assertEquals('Please select a category', $errors['category'][0]);
        $this->assertEquals('Please select a district', $errors['district'][0]);
        $this->assertEquals('Description should be at least 50 characters', $errors['description'][0]);
        $this->assertEquals('Please enter a valid phone number', $errors['phone'][0]);
        $this->assertEquals('Please enter a valid email address', $errors['email'][0]);
        $this->assertEquals('Please enter a valid website URL', $errors['website'][0]);
        $this->assertEquals('Business address is required', $errors['address'][0]);
        $this->assertEquals('Password must be at least 8 characters', $errors['password'][0]);
    }
}
