<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test payment initiation
     */
    public function test_can_initiate_payment(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a business for the user
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'package_type' => 'Free',
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Set up PayFast config for testing
        Config::set('payfast.merchant_id', 'test_merchant_id');
        Config::set('payfast.merchant_key', 'test_merchant_key');
        Config::set('payfast.sandbox', true);

        // Make the request to initiate payment
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/payments/initiate', [
            'business_id' => $business->id,
            'package_id' => 'Silver',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'payment_id',
                'payfast_data',
                'payfast_url',
            ]);

        // Get the payment ID from the response
        $paymentId = $response->json('payment_id');
        
        // Verify the payment exists in the database
        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'user_id' => $user->id,
            'business_id' => $business->id,
            'package_id' => 'Silver',
        ]);
        
        // Get the payment from the database to check the amount
        $payment = Payment::find($paymentId);
        $this->assertEquals(499.99, (float)$payment->amount);
    }

    /**
     * Test payment initiation validation
     */
    public function test_payment_initiation_validation(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Create a token for the user
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the request with missing data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/payments/initiate', []);

        // It should fail validation
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['business_id', 'package_id']);

        // Make the request with invalid business ID
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/payments/initiate', [
            'business_id' => 9999, // Non-existent business
            'package_id' => 'Silver',
        ]);

        // It should fail validation
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['business_id']);
    }

    /**
     * Test PayFast notification (ITN) handling
     */
    public function test_payfast_notification_handling(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a business
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'package_type' => 'Free',
        ]);

        // Create a pending payment using the factory
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'package_id' => 'Silver',
            'amount' => 499.99,
            'currency' => 'ZAR',
            'payment_method' => 'credit_card',
            'status' => 'pending',
        ]);

        // Simulate a PayFast ITN notification
        $itnData = [
            'm_payment_id' => $payment->id,
            'pf_payment_id' => 'PF_' . time(),
            'payment_status' => 'COMPLETE',
            'item_name' => 'Business Package Upgrade: Silver',
            'amount_gross' => '499.99',
            'signature' => 'test_signature',
        ];

        // Make the request to the notify endpoint
        $response = $this->postJson('/api/payfast/notify', $itnData);

        // The response should be OK
        $response->assertOk();

        // Refresh the payment from the database
        $payment->refresh();

        // Verify the payment was updated
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals($itnData['pf_payment_id'], $payment->transaction_id);

        // Verify the business package was updated
        $business->refresh();
        $this->assertEquals('Silver', $business->package_type);
    }

    /**
     * Test payment success redirect
     */
    public function test_payment_success_redirect(): void
    {
        // Create a session to avoid the session table error
        $this->withSession(['key' => 'value']);
        
        // Make a request to the success endpoint
        $response = $this->get('/api/payfast/return');

        // It should redirect to the dashboard
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test payment cancel redirect
     */
    public function test_payment_cancel_redirect(): void
    {
        // Create a session to avoid the session table error
        $this->withSession(['key' => 'value']);
        
        // Make a request to the cancel endpoint
        $response = $this->get('/api/payfast/cancel');

        // It should redirect to the dashboard
        $response->assertRedirect('/dashboard');
    }
}
