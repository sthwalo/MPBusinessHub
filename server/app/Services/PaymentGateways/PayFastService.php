<?php

namespace App\Services\PaymentGateways;

use App\Models\Business;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class PayFastService
{
    protected $merchantId;
    protected $merchantKey;
    protected $passphrase;
    protected $testMode;
    protected $returnUrl;
    protected $cancelUrl;
    protected $notifyUrl;
    protected $apiUrl;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Load configuration from .env file
        $this->merchantId = config('services.payfast.merchant_id');
        $this->merchantKey = config('services.payfast.merchant_key');
        $this->passphrase = config('services.payfast.passphrase');
        $this->testMode = config('services.payfast.test_mode', true);
        
        // Set URLs
        $baseUrl = config('app.url');
        $this->returnUrl = $baseUrl . '/payment/success';
        $this->cancelUrl = $baseUrl . '/payment/cancel';
        $this->notifyUrl = $baseUrl . '/api/payment/notify';
        
        // Set API URL based on test mode
        $this->apiUrl = $this->testMode 
            ? 'https://sandbox.payfast.co.za/eng/process' 
            : 'https://www.payfast.co.za/eng/process';
    }
    
    /**
     * Generate payment URL for PayFast redirect
     *
     * @param Invoice $invoice
     * @param Business $business
     * @return array
     */
    public function generatePaymentUrl(Invoice $invoice, Business $business)
    {
        try {
            // Get user details
            $user = $business->user;
            
            if (!$user) {
                throw new \Exception('No user associated with this business');
            }
            
            // Format amount (PayFast requires amount in ZAR with 2 decimal places)
            $amount = number_format($invoice->amount, 2, '.', '');
            
            // Build payment data
            $data = [
                // Merchant details
                'merchant_id' => $this->merchantId,
                'merchant_key' => $this->merchantKey,
                
                // Transaction details
                'amount' => $amount,
                'item_name' => "Invoice #{$invoice->id} - {$invoice->description}",
                'item_description' => $invoice->description,
                
                // Custom variables
                'custom_str1' => $invoice->id, // Invoice ID
                'custom_str2' => $business->id, // Business ID
                'custom_str3' => $invoice->change_type ?? 'upgrade', // Change type
                
                // Buyer details
                'name_first' => $user->first_name ?? $user->name ?? '',
                'name_last' => $user->last_name ?? '',
                'email_address' => $user->email,
                
                // URLs
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl,
                'notify_url' => $this->notifyUrl,
                
                // Options
                'email_confirmation' => 1,
                'confirmation_address' => $user->email
            ];
            
            // Generate signature
            $signature = $this->generateSignature($data);
            $data['signature'] = $signature;
            
            // Add test mode parameter if in test mode
            if ($this->testMode) {
                $data['testing'] = 'true';
            }
            
            return [
                'success' => true,
                'payment_url' => $this->apiUrl,
                'payment_data' => $data,
                'method' => 'POST'
            ];
            
        } catch (\Exception $e) {
            Log::error('PayFast payment URL generation error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'business_id' => $business->id
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to generate PayFast payment URL: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate signature for PayFast request
     *
     * @param array $data
     * @return string
     */
    protected function generateSignature($data)
    {
        // Sort data alphabetically by key
        ksort($data);
        
        // URL encode the data
        $encodedData = http_build_query($data);
        
        // Add passphrase if set
        if (!empty($this->passphrase)) {
            $encodedData .= '&passphrase=' . urlencode($this->passphrase);
        }
        
        // Generate MD5 hash
        return md5($encodedData);
    }
    
    /**
     * Verify PayFast ITN (Instant Transaction Notification)
     *
     * @param array $postData
     * @return bool
     */
    public function verifyItn($postData)
    {
        try {
            // Verify source IP
            if (!$this->isValidPayFastIP()) {
                Log::warning('PayFast ITN received from invalid IP', [
                    'ip' => request()->ip()
                ]);
                return false;
            }
            
            // Verify data format
            if (empty($postData['merchant_id']) || empty($postData['signature'])) {
                Log::warning('PayFast ITN missing required fields', [
                    'data' => $postData
                ]);
                return false;
            }
            
            // Verify merchant ID
            if ($postData['merchant_id'] !== $this->merchantId) {
                Log::warning('PayFast ITN has invalid merchant ID', [
                    'received' => $postData['merchant_id'],
                    'expected' => $this->merchantId
                ]);
                return false;
            }
            
            // Get signature from post data
            $receivedSignature = $postData['signature'];
            unset($postData['signature']);
            
            // Calculate signature
            $calculatedSignature = $this->generateSignature($postData);
            
            // Verify signature
            if ($receivedSignature !== $calculatedSignature) {
                Log::warning('PayFast ITN has invalid signature', [
                    'received' => $receivedSignature,
                    'calculated' => $calculatedSignature
                ]);
                return false;
            }
            
            // Verify payment status
            if ($postData['payment_status'] !== 'COMPLETE') {
                Log::warning('PayFast ITN payment not complete', [
                    'status' => $postData['payment_status']
                ]);
                return false;
            }
            
            // Verify with PayFast API
            if (!$this->verifyWithPayFastApi($postData)) {
                Log::warning('PayFast ITN verification failed with PayFast API');
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('PayFast ITN verification error: ' . $e->getMessage(), [
                'data' => $postData
            ]);
            return false;
        }
    }
    
    /**
     * Verify if the request is coming from a valid PayFast IP
     *
     * @return bool
     */
    protected function isValidPayFastIP()
    {
        $validIps = [
            '197.97.145.144', // PayFast production
            '197.97.145.145', // PayFast production
            '197.97.145.146', // PayFast production
            '41.74.179.194',  // PayFast sandbox
            '127.0.0.1'       // Local testing
        ];
        
        return in_array(request()->ip(), $validIps);
    }
    
    /**
     * Verify ITN with PayFast API
     *
     * @param array $postData
     * @return bool
     */
    protected function verifyWithPayFastApi($postData)
    {
        // Construct validation URL
        $validationUrl = $this->testMode 
            ? 'https://sandbox.payfast.co.za/eng/query/validate' 
            : 'https://www.payfast.co.za/eng/query/validate';
        
        // Send validation request to PayFast
        $response = Http::asForm()->post($validationUrl, $postData);
        
        // Check response
        return trim($response->body()) === 'VALID';
    }
}
