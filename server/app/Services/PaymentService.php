<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PackageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    protected $packageService;
    protected $currency = 'ZAR'; // Default currency for South Africa

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
     * @param string $paymentMethod
     * @return array
     */
    public function processPackageChange(Business $business, $packageId, $billingCycle = 'monthly', $paymentMethod = 'credit_card')
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            // Get the new package
            $newPackage = $this->packageService->getPackageById($packageId);
            if (!$newPackage) {
                throw new \Exception('Package not found');
            }
            
            // Get the comparison between current and new package
            $packageComparison = $this->packageService->comparePackages(
                $business->package_id, 
                $packageId
            );
            
            // Calculate the amount to charge
            $amount = $this->packageService->calculateProratedAmount($business, $newPackage, $billingCycle);
            
            // Create an invoice
            $invoice = new Invoice();
            $invoice->business_id = $business->id;
            $invoice->amount = $amount;
            $invoice->currency = $this->currency;
            $invoice->package_id = $packageId;
            $invoice->billing_cycle = $billingCycle;
            $invoice->change_type = $packageComparison ?? 'new'; // upgrade, downgrade, same, or new
            $invoice->description = "Package change to {$newPackage->name} ({$billingCycle})"; 
            $invoice->status = 'pending';
            $invoice->save();
            
            // If amount is zero (e.g., downgrade with credit), mark as paid without payment processing
            if ($amount <= 0) {
                $invoice->status = 'paid';
                $invoice->payment_method = 'credit';
                $invoice->save();
                
                // Update the business subscription
                $this->updateBusinessSubscription($business, $newPackage, $billingCycle);
                
                // Commit the transaction
                DB::commit();
                
                // Send confirmation email
                $this->sendPaymentNotification($business, $invoice, 'success');
                
                return [
                    'success' => true,
                    'message' => 'Plan changed successfully using account credit',
                    'invoice_id' => $invoice->id,
                    'amount' => 0
                ];
            }
            
            // Process the payment (this would integrate with a payment gateway)
            $paymentResult = $this->processPayment($business, $invoice, $amount, $paymentMethod);
            
            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }
            
            // Update the invoice status
            $invoice->status = 'paid';
            $invoice->payment_method = $paymentMethod;
            $invoice->transaction_id = $paymentResult['transaction_id'] ?? null;
            $invoice->save();
            
            // Update the business subscription
            $this->updateBusinessSubscription($business, $newPackage, $billingCycle);
            
            // Commit the transaction
            DB::commit();
            
            // Send confirmation email
            $this->sendPaymentNotification($business, $invoice, 'success');
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'currency' => $this->currency,
                'transaction_id' => $paymentResult['transaction_id'] ?? null
            ];
            
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'package_id' => $packageId,
                'billing_cycle' => $billingCycle,
                'payment_method' => $paymentMethod
            ]);
            
            // If we created an invoice, update its status to failed
            if (isset($invoice) && $invoice->id) {
                $invoice->status = 'failed';
                $invoice->notes = $e->getMessage();
                $invoice->save();
                
                // Send failure notification
                $this->sendPaymentNotification($business, $invoice, 'failure', $e->getMessage());
            }
            
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'error_code' => $e->getCode()
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
     * @param string $paymentMethod
     * @return array
     */
    protected function processPayment(Business $business, Invoice $invoice, $amount, $paymentMethod = 'credit_card')
    {
        // This is where you would integrate with a South African payment gateway
        // Popular options include:
        // - PayFast
        // - Peach Payments
        // - Yoco
        // - SnapScan
        // - Ozow (formerly i-Pay)
        
        try {
            // Simulate different payment methods
            $paymentGateway = $this->getPaymentGateway($paymentMethod);
            
            if (!$paymentGateway) {
                throw new \Exception("Payment method '{$paymentMethod}' is not supported");
            }
            
            // In a real implementation, this would make an API call to the payment gateway
            // For now, we'll simulate a successful payment
            $transactionId = $this->simulatePaymentGateway($paymentMethod, $amount);
            
            // Create a payment record
            $payment = new Payment();
            $payment->business_id = $business->id;
            $payment->invoice_id = $invoice->id;
            $payment->amount = $amount;
            $payment->currency = $this->currency;
            $payment->payment_method = $paymentMethod;
            $payment->payment_gateway = $paymentGateway;
            $payment->transaction_id = $transactionId;
            $payment->status = 'completed';
            $payment->save();
            
            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'gateway' => $paymentGateway
            ];
            
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Get the payment gateway based on payment method
     *
     * @param string $paymentMethod
     * @return string|null
     */
    protected function getPaymentGateway($paymentMethod)
    {
        $gateways = [
            'credit_card' => 'PayFast',
            'debit_card' => 'PayFast',
            'eft' => 'Ozow',
            'instant_eft' => 'Ozow',
            'mobicred' => 'PayFast',
            'snapscan' => 'SnapScan',
            'zapper' => 'Zapper'
        ];
        
        return $gateways[$paymentMethod] ?? null;
    }
    
    /**
     * Simulate a payment gateway response
     * In production, this would be replaced with actual API calls
     *
     * @param string $paymentMethod
     * @param float $amount
     * @return string
     */
    protected function simulatePaymentGateway($paymentMethod, $amount)
    {
        // Generate a unique transaction ID based on the payment method
        $prefix = substr($paymentMethod, 0, 3);
        return strtoupper($prefix) . '_' . uniqid() . '_' . date('Ymd');
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
        
        // Update product limit if applicable
        if (isset($package->product_limit)) {
            $business->product_limit = $package->product_limit;
        }
        
        // Update social features based on package
        if ($package->name === 'Gold') {
            $business->social_features_remaining = 2; // 2 social media features per month for Gold
        } else if ($package->name === 'Silver') {
            $business->social_features_remaining = 1; // 1 social media feature per month for Silver
        } else {
            $business->social_features_remaining = 0;
        }
        
        $business->save();
        
        return $business;
    }
    
    /**
     * Send payment notification email
     *
     * @param Business $business
     * @param Invoice $invoice
     * @param string $type success|failure
     * @param string $errorMessage
     * @return void
     */
    protected function sendPaymentNotification(Business $business, Invoice $invoice, $type = 'success', $errorMessage = null)
    {
        // In a real implementation, this would send an email to the business owner
        // For now, we'll just log it
        
        $user = $business->user;
        
        if (!$user || !$user->email) {
            Log::warning('Cannot send payment notification: no user email found', [
                'business_id' => $business->id,
                'invoice_id' => $invoice->id
            ]);
            return;
        }
        
        $subject = $type === 'success' 
            ? "Payment Successful - {$business->name}" 
            : "Payment Failed - {$business->name}";
            
        $message = $type === 'success'
            ? "Your payment of {$this->currency} {$invoice->amount} for the {$invoice->package->name} package was successful."
            : "Your payment failed: {$errorMessage}. Please try again or contact support.";
        
        Log::info("Would send email: {$subject} to {$user->email}", [
            'message' => $message,
            'business_id' => $business->id,
            'invoice_id' => $invoice->id
        ]);
        
        // In production, uncomment this to send actual emails
        /*
        Mail::send('emails.payment_notification', [
            'business' => $business,
            'invoice' => $invoice,
            'type' => $type,
            'message' => $message
        ], function ($mail) use ($user, $subject) {
            $mail->to($user->email, $user->name)
                ->subject($subject);
        });
        */
    }
}
