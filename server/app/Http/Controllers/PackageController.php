<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    /**
     * Get all available packages
     */
    public function index(): JsonResponse
    {
        try {
            Log::info('Fetching all active packages');
            $packages = Package::where('is_active', true)->get();
            
            if ($packages->isEmpty()) {
                Log::warning('No active packages found in the database');
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active packages found'
                ], 404);
            }
            
            Log::info('Successfully retrieved ' . $packages->count() . ' packages');
            return response()->json([
                'status' => 'success',
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve packages: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upgrade business package
     */
    public function upgrade(Request $request): JsonResponse
    {
        try {
            Log::info('Package upgrade request received', ['request_data' => $request->all()]);
            
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:packages,id',
                'billing_cycle' => 'required|string|in:monthly,annual',
            ]);
            
            if ($validator->fails()) {
                Log::warning('Package upgrade validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = Auth::user();
            Log::info('Processing package upgrade for user', ['user_id' => $user->id, 'email' => $user->email]);
            
            $business = Business::where('user_id', $user->id)->first();
            
            if (!$business) {
                Log::warning('Business not found for user during package upgrade', ['user_id' => $user->id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }
            
            $package = Package::findOrFail($request->package_id);
            Log::info('Found package for upgrade', [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'business_id' => $business->id,
                'billing_cycle' => $request->billing_cycle
            ]);
            
            // Store previous values for logging
            $previousPackageId = $business->package_id;
            $previousBillingCycle = $business->billing_cycle;
            
            // Update business package
            $business->package_id = $package->id;
            $business->package_type = $package->name; // Make sure package_type is also updated
            $business->billing_cycle = $request->billing_cycle;
            $business->adverts_remaining = $package->advert_limit;
            
            // Set subscription end date (30 days for monthly, 365 days for annual)
            $days = $request->billing_cycle === 'monthly' ? 30 : 365;
            $business->subscription_ends_at = now()->addDays($days);
            
            // Set social features based on package
            if ($package->name === 'Gold') {
                $business->social_features_remaining = 2; // 2 social media features per month for Gold
            } else if ($package->name === 'Silver') {
                $business->social_features_remaining = 1; // 1 social media feature per month for Silver
            } else {
                $business->social_features_remaining = 0;
            }
            
            $business->save();
            
            Log::info('Business package upgraded successfully', [
                'business_id' => $business->id,
                'previous_package_id' => $previousPackageId,
                'new_package_id' => $package->id,
                'previous_billing_cycle' => $previousBillingCycle,
                'new_billing_cycle' => $request->billing_cycle,
                'subscription_ends_at' => $business->subscription_ends_at
            ]);
            
            // In a real implementation, I will handle payment processing here
            // and only update the package after successful payment
            
            return response()->json([
                'status' => 'success',
                'message' => 'Package upgraded successfully',
                'data' => [
                    'package' => $package,
                    'business' => [
                        'id' => $business->id,
                        'package_id' => $business->package_id,
                        'package_type' => $business->package_type,
                        'billing_cycle' => $business->billing_cycle,
                        'subscription_ends_at' => $business->subscription_ends_at,
                        'adverts_remaining' => $business->adverts_remaining,
                        'social_features_remaining' => $business->social_features_remaining
                    ]
                ]
           ]);
        } catch (\Exception $e) {
            Log::error('Error upgrading package: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while upgrading package: ' . $e->getMessage()
            ], 500);
        }
    }
}