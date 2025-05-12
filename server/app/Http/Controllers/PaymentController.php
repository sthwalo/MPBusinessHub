<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentGateways\PayFastService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;
    
    /**
     * Constructor
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Handle PayFast ITN (Instant Transaction Notification)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handlePayFastNotification(Request $request)
    {
        Log::info('PayFast ITN received', ['data' => $request->all()]);
        
        try {
            // Verify the ITN
            $payFastService = app(PayFastService::class);
            $isValid = $payFastService->verifyItn($request->all());
            
            if (!$isValid) {
                Log::warning('Invalid PayFast ITN received');
                return response('Invalid ITN', 400);
            }
            
            // Get invoice and payment details
            $invoiceId = $request->input('custom_str1');
            $businessId = $request->input('custom_str2');
            
            $invoice = Invoice::find($invoiceId);
            $business = Business::find($businessId);
            
            if (!$invoice || !$business) {
                Log::error('Invoice or business not found for PayFast ITN', [
                    'invoice_id' => $invoiceId,
                    'business_id' => $businessId
                ]);
                return response('Invoice or business not found', 404);
            }
            
            // Find the pending payment
            $payment = Payment::where('invoice_id', $invoice->id)
                ->where('business_id', $business->id)
                ->where('status', 'pending')
                ->first();
            
            if (!$payment) {
                Log::error('No pending payment found for PayFast ITN', [
                    'invoice_id' => $invoice->id,
                    'business_id' => $business->id
                ]);
                return response('No pending payment found', 404);
            }
            
            // Update payment details
            $payment->status = 'completed';
            $payment->transaction_id = $request->input('pf_payment_id');
            $payment->notes = 'PayFast payment completed';
            $payment->save();
            
            // Update invoice status
            $invoice->status = 'paid';
            $invoice->save();
            
            // Update business subscription
            $this->paymentService->updateBusinessSubscription($business, $invoice->package);
            
            // Send payment notification
            $this->paymentService->sendPaymentNotification($business, $invoice, 'success');
            
            return response('OK');
            
        } catch (\Exception $e) {
            Log::error('Error processing PayFast ITN: ' . $e->getMessage(), [
                'data' => $request->all()
            ]);
            
            return response('Error: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Handle payment success redirect
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentSuccess(Request $request)
    {
        // This is where the user is redirected after successful payment
        // We don't process the payment here, as it's already processed in the ITN handler
        // Just show a success message
        
        return redirect('/dashboard')->with('success', 'Payment successful! Your subscription has been updated.');
    }
    
    /**
     * Handle payment cancellation redirect
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentCancel(Request $request)
    {
        // This is where the user is redirected after cancelling payment
        
        return redirect('/dashboard')->with('warning', 'Payment was cancelled. Your subscription has not been changed.');
    }
}
