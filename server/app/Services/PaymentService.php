<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PackageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $packageService;

    /**
     * Constructor
     */
    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Process a package upgrade/downgrade payment
     *
     * @param Business $business
     * @param int $packageId
     * @param string $billingCycle
     * @return array
     */
    public function processPackageChange(Business $business, $packageId, $billingCycle = 'monthly')
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            // Get the new package
            $newPackage = $this->packageService->getPackageById($packageId);
            if (!$newPackage) {
                throw new \Exception('Package not found');
            }
            
            // Calculate the amount to charge
            $amount = $this->packageService->calculateProratedAmount($business, $newPackage, $billingCycle);
            
            // Create an invoice
            $invoice = new Invoice();
            $invoice->business_id = $business->id;
            $invoice->amount = $amount;
            $invoice->description = "Package change to {$newPackage->name} ({$billingCycle})"; 
            $invoice->status = 'pending';
            $invoice->save();
            
            // Process the payment (this would integrate with a payment gateway)
            $paymentResult = $this->processPayment($business, $invoice, $amount);
            
            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }
            
            // Update the invoice status
            $invoice->status = 'paid';
            $invoice->save();
            
            // Update the business subscription
            $this->updateBusinessSubscription($business, $newPackage, $billingCycle);
            
            // Commit the transaction
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'invoice_id' => $invoice->id,
                'amount' => $amount
            ];
            
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'package_id' => $packageId,
                'billing_cycle' => $billingCycle
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process a payment through a payment gateway
     * This is a placeholder for actual payment gateway integration
     *
     * @param Business $business
     * @param Invoice $invoice
     * @param float $amount
     * @return array
     */
    protected function processPayment(Business $business, Invoice $invoice, $amount)
    {
        // This is where you would integrate with a payment gateway like Stripe, PayPal, etc.
        // For now, we'll simulate a successful payment
        
        try {
            // Create a payment record
            $payment = new Payment();
            $payment->business_id = $business->id;
            $payment->invoice_id = $invoice->id;
            $payment->amount = $amount;
            $payment->payment_method = 'credit_card'; // This would come from the actual payment method used
            $payment->transaction_id = 'sim_' . uniqid(); // This would come from the payment gateway
            $payment->status = 'completed';
            $payment->save();
            
            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id
            ];
            
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update the business subscription details
     *
     * @param Business $business
     * @param Package $package
     * @param string $billingCycle
     * @return Business
     */
    protected function updateBusinessSubscription(Business $business, $package, $billingCycle)
    {
        // Calculate the new subscription end date
        $now = now();
        $subscriptionEndsAt = $billingCycle === 'annual' 
            ? $now->copy()->addYear() 
            : $now->copy()->addMonth();
        
        // Update the business
        $business->package_id = $package->id;
        $business->package_type = $package->name;
        $business->billing_cycle = $billingCycle;
        $business->subscription_ends_at = $subscriptionEndsAt;
        
        // Update the advert limit based on the new package
        $business->adverts_remaining = $package->advert_limit;
        
        $business->save();
        
        return $business;
    }
}
