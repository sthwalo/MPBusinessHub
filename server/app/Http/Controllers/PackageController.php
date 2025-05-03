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
        $packages = Package::where('is_active', true)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $packages
        ]);
    }
    
    /**
     * Upgrade business package
     */
    public function upgrade(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:packages,id',
                'billing_cycle' => 'required|string|in:monthly,annual',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = Auth::user();
            $business = Business::where('user_id', $user->id)->first();
            
            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }
            
            $package = Package::findOrFail($request->package_id);
            
            // Update business package
            $business->package_id = $package->id;
            $business->billing_cycle = $request->billing_cycle;
            $business->adverts_remaining = $package->advert_limit;
            
            // Set subscription end date (30 days for monthly, 365 days for annual)
            $days = $request->billing_cycle === 'monthly' ? 30 : 365;
            $business->subscription_ends_at = now()->addDays($days);
            
            $business->save();
            
            // In a real implementation, you would handle payment processing here
            // and only update the package after successful payment
            
            return response()->json([
                'status' => 'success',
                'message' => 'Package upgraded successfully',
                'data' => [
                    'package' => $package,
                    'business' => [
                        'id' => $business->id,
                        'package_id' => $business->package_id,
                        'billing_cycle' => $business->billing_cycle,
                        'subscription_ends_at' => $business->subscription_ends_at,
                        'adverts_remaining' => $business->adverts_remaining
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error upgrading package: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while upgrading package: ' . $e->getMessage()
            ], 500);
        }
    }
}