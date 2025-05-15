<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving products for a business
     */
    public function test_can_get_business_products(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver', // Package that allows products
        ]);

        // Create some products for the business
        for ($i = 0; $i < 3; $i++) {
            Product::create([
                'business_id' => $business->id,
                'name' => "Test Product {$i}",
                'description' => "Description for product {$i}",
                'price' => 10.99 + $i,
                'status' => 'active',
            ]);
        }

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request to get products
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
            
        // Check that we have 3 products in the response
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test retrieving products for a specific business by ID
     */
    public function test_can_get_products_by_business_id(): void
    {
        // Create a user for authentication
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Create a business
        $business = Business::factory()->create([
            'status' => 'approved',
        ]);

        // Create some products for the business
        for ($i = 0; $i < 3; $i++) {
            Product::create([
                'business_id' => $business->id,
                'name' => "Test Product {$i}",
                'description' => "Description for product {$i}",
                'price' => 10.99 + $i,
                'status' => 'active',
            ]);
        }

        // Create some products for another business
        $otherBusiness = Business::factory()->create();
        for ($i = 0; $i < 2; $i++) {
            Product::create([
                'business_id' => $otherBusiness->id,
                'name' => "Other Product {$i}",
                'description' => "Description for other product {$i}",
                'price' => 20.99 + $i,
                'status' => 'active',
            ]);
        }

        // Make the request to get products for the first business
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/products?business_id={$business->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
            
        // Check that we have 3 products in the response
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test creating a new product
     */
    public function test_can_create_product(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Silver package
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Silver',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare product data
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'image_url' => 'https://example.com/image.jpg',
            'is_featured' => true,
        ];

        // Make the request to create a product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => [
                    'name' => 'Test Product',
                    'description' => 'This is a test product',
                    'price' => 99.99,
                    'is_featured' => true,
                    'status' => 'active',
                ]
            ]);

        // Verify the product was created in the database
        $this->assertDatabaseHas('products', [
            'business_id' => $business->id,
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'image_url' => 'https://example.com/image.jpg',
            'is_featured' => 1,
            'status' => 'active',
        ]);
    }

    /**
     * Test product creation validation
     */
    public function test_product_creation_validation(): void
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

        // Prepare invalid product data
        $invalidData = [
            'name' => '', // Empty name should fail validation
            'price' => 'not-a-number', // Invalid price
        ];

        // Make the request to create a product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/products', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    /**
     * Test package restriction for product creation
     */
    public function test_package_restriction_for_product_creation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user with Bronze package (doesn't allow products)
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Free', // Using Basic instead of Bronze to avoid constraint violation
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare product data
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
        ];

        // Make the request to create a product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your package does not support adding products. Please upgrade to Silver or Gold.'
            ]);
    }

    /**
     * Test updating a product
     */
    public function test_can_update_product(): void
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

        // Create a product for the business
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'name' => 'Original Product',
            'description' => 'Description for original product',
            'price' => 50.00,
            'status' => 'active',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare update data
        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description for original product',
            'price' => 75.00,
            'is_featured' => true,
        ];

        // Make the request to update the product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product updated successfully',
            ]);

        // Verify the product was updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 75.00,
            'is_featured' => 1,
        ]);
    }

    /**
     * Test updating a product that doesn't belong to the user's business
     */
    public function test_cannot_update_other_business_product(): void
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

        // Create another business with a product
        $otherBusiness = Business::factory()->create();
        $product = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Product',
            'description' => 'Description for other product',
            'price' => 20.99,
            'status' => 'active',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Prepare update data
        $updateData = [
            'name' => 'Updated Product',
        ];

        // Make the request to update the product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(404);
    }

    /**
     * Test getting a specific product
     */
    public function test_can_get_specific_product(): void
    {
        // Create a user for authentication
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Description for test product',
            'price' => 39.99,
            'status' => 'active',
        ]);

        // Make the request to get the product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                ]
            ]);
    }

    /**
     * Test getting a non-existent product
     */
    public function test_get_nonexistent_product(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request to get a non-existent product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/products/9999");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Product not found'
            ]);
    }

    /**
     * Test deleting a product
     */
    public function test_can_delete_product(): void
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

        // Create a product for the business
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'name' => 'Product to delete',
            'description' => 'Description for product to delete',
            'price' => 15.99,
            'status' => 'active',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request to delete the product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ]);

        // Verify the product was deleted from the database
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test deleting a product that doesn't belong to the user's business
     */
    public function test_cannot_delete_other_business_product(): void
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

        // Create another business with a product
        $otherBusiness = Business::factory()->create();
        $product = Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Business Product',
            'description' => 'Description for other business product',
            'price' => 29.99,
            'status' => 'active',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request to delete the product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(404);

        // Verify the product still exists in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }
}
