<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test business image upload
     */
    public function test_can_upload_business_image(): void
    {
        // Create fake storage disk
        Storage::fake('public');

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

        // Create a fake image
        $image = UploadedFile::fake()->image('business.jpg', 1000, 1000);

        // Make the request to upload the business image
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/business', [
            'image' => $image,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'image_url',
            ]);

        // Assert the file was stored
        $path = 'business_images/' . time() . '_' . $business->id . '.jpg';
        Storage::disk('public')->assertExists($path);

        // Refresh the business from the database
        $business->refresh();

        // Assert the business image_url was updated
        $this->assertNotNull($business->image_url);
    }

    /**
     * Test business image upload validation
     */
    public function test_business_image_upload_validation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request without an image
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/business', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Please provide a valid image file',
            ])
            ->assertJsonValidationErrors(['image']);
    }

    /**
     * Test business image upload for non-existent business
     */
    public function test_business_image_upload_no_business(): void
    {
        // Create fake storage disk
        Storage::fake('public');

        // Create a user without a business
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Create a fake image
        $image = UploadedFile::fake()->image('business.jpg');

        // Make the request to upload the business image
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/business', [
            'image' => $image,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Business not found',
            ]);
    }

    /**
     * Test product image upload
     */
    public function test_can_upload_product_image(): void
    {
        // Create fake storage disk
        Storage::fake('public');

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
        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'status' => 'active',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Create a fake image
        $image = UploadedFile::fake()->image('product.jpg');

        // Make the request to upload the product image
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/product', [
            'image' => $image,
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product image uploaded successfully',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'image_url',
            ]);

        // Assert the file was stored
        Storage::disk('public')->assertExists('product_images/' . time() . '_product_' . $product->id . '.jpg');

        // Refresh the product from the database
        $product->refresh();

        // Assert the product image_url was updated
        $this->assertNotNull($product->image_url);
    }

    /**
     * Test product image upload validation
     */
    public function test_product_image_upload_validation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        // Create a product for the business
        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'status' => 'active',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request without an image
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/product', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Please provide a valid image file and product ID',
            ])
            ->assertJsonValidationErrors(['image']);

        // Make the request without a product_id
        $image = UploadedFile::fake()->image('product.jpg');
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/product', [
            'image' => $image,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Please provide a valid image file and product ID',
            ])
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Test product image upload for product not owned by user
     */
    public function test_product_image_upload_not_owned(): void
    {
        // Create fake storage disk
        Storage::fake('public');

        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create another user with a business and product
        $otherUser = User::factory()->create();
        $otherBusiness = Business::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'approved',
        ]);
        $otherProduct = Product::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Product',
            'description' => 'Other product description',
            'price' => 49.99,
            'status' => 'active',
        ]);

        // Create a token for the first user
        $token = $user->createToken('test-token')->plainTextToken;

        // Create a fake image
        $image = UploadedFile::fake()->image('product.jpg');

        // Make the request to upload an image to the other user's product
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/images/product', [
            'image' => $image,
            'product_id' => $otherProduct->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You do not have permission to upload images for this product',
            ]);
    }
}
