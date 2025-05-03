<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayFastController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'package_id' => 'required|string'
        ]);

        $user = Auth::user();
        $business = Business::findOrFail($request->business_id);
        
        // Package prices (in a real app, these would come from a database)
        $packages = [
            'basic' => 0,
            'premium' => 199.99,
            'enterprise' => 499.99
        ];
        
        $amount = $packages[$request->package_id] ?? 0;
        
        // Create a pending payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'package_id' => $request->package_id,
            'amount' => $amount,
            'currency' => 'ZAR',
            'payment_method' => 'credit_card',
            'status' => 'pending'
        ]);
        
        // PayFast API integration would go here
        // This is where you would generate the PayFast form data
        
        // For demonstration, we'll return what would be needed for the PayFast form
        $payfast_data = [
            'merchant_id' => config('payfast.merchant_id'),
            'merchant_key' => config('payfast.merchant_key'),
            'return_url' => route('payfast.return'),
            'cancel_url' => route('payfast.cancel'),
            'notify_url' => route('payfast.notify'),
            'name_first' => $user->name,
            'email_address' => $user->email,
            'amount' => $amount,
            'm_payment_id' => $payment->id,
            'item_name' => "Business Package Upgrade: {$request->package_id}"
        ];
        
        return response()->json([
            'status' => 'success',
            'payment_id' => $payment->id,
            'payfast_data' => $payfast_data,
            'payfast_url' => config('payfast.sandbox') ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process'
        ]);
    }
    
    public function notify(Request $request)
    {
        // This is where PayFast will send ITN (Instant Transaction Notifications)
        Log::info('PayFast ITN received', $request->all());
        
        // Verify the payment with PayFast
        // Update the payment status
        $payment = Payment::where('id', $request->m_payment_id)->first();
        
        if ($payment) {
            $payment->status = 'completed';
            $payment->transaction_id = $request->pf_payment_id;
            $payment->payfast_data = $request->all();
            $payment->save();
            
            // Update the business package
            $business = Business::find($payment->business_id);
            if ($business) {
                $business->package_type = $payment->package_id;
                $business->save();
            }
        }
        
        return response('OK');
    }
    
    public function return(Request $request)
    {
        // User is redirected here after successful payment
        return redirect()->route('dashboard')->with('success', 'Payment successful! Your business package has been upgraded.');
    }
    
    public function cancel(Request $request)
    {
        // User is redirected here after cancelling payment
        return redirect()->route('dashboard')->with('error', 'Payment cancelled. Your business package has not been upgraded.');
    }
}