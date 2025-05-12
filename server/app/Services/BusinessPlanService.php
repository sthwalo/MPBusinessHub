<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Package;
use Illuminate\Support\Facades\Log;

class BusinessPlanService
{
    protected $paymentService;
    
    /**
     * Constructor
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Upgrade a business plan
     *
     * @param Business $business
     * @param int $packageId
     * @param string $paymentMethod
     * @param string $billingCycle
     * @return array
     */
    public function upgradePlan(Business $business, $packageId, $paymentMethod, $billingCycle)
    {
        try {
            // Get the package
            $package = Package::find($packageId);
            
            if (!$package) {
                return [
                    'success' => false,
                    'message' => 'Package not found'
                ];
            }
            
            // Process the package change
            $result = $this->paymentService->processPackageChange(
                $business,
                $package,
                $paymentMethod,
                $billingCycle
            );
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error upgrading business plan: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'package_id' => $packageId,
                'payment_method' => $paymentMethod,
                'billing_cycle' => $billingCycle
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while upgrading the business plan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get available packages for a business
     *
     * @param Business $business
     * @return array
     */
    public function getAvailablePackages(Business $business)
    {
        try {
            // Get all active packages
            $packages = Package::where('is_active', true)->get();
            
            // If no active packages, get all packages
            if ($packages->isEmpty()) {
                $packages = Package::all();
            }
            
            // Get the current package
            $currentPackage = $business->package;
            
            // Format packages for the frontend
            $formattedPackages = $packages->map(function ($package) use ($currentPackage) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'type' => $package->type,
                    'description' => $package->description,
                    'monthly_price' => $package->monthly_price,
                    'annual_price' => $package->annual_price,
                    'features' => [
                        'product_limit' => $package->product_limit,
                        'social_media_limit' => $package->social_media_limit,
                        'has_analytics' => $package->has_analytics,
                        'has_premium_support' => $package->has_premium_support
                    ],
                    'is_current' => $currentPackage && $currentPackage->id === $package->id
                ];
            });
            
            return [
                'success' => true,
                'data' => $formattedPackages
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting available packages: ' . $e->getMessage(), [
                'business_id' => $business->id
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving available packages: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate prorated amount for package change
     *
     * @param Business $business
     * @param Package $newPackage
     * @param string $billingCycle
     * @return array
     */
    public function calculateProratedAmount(Business $business, Package $newPackage, $billingCycle)
    {
        try {
            // Get the current package
            $currentPackage = $business->package;
            
            // If no current package, return the full price of the new package
            if (!$currentPackage) {
                $amount = $billingCycle === 'annual' ? $newPackage->annual_price : $newPackage->monthly_price;
                
                return [
                    'success' => true,
                    'amount' => $amount,
                    'is_prorated' => false
                ];
            }
            
            // Get the current subscription end date
            $subscriptionEndDate = $business->subscription_end_date;
            
            // If no subscription end date, return the full price of the new package
            if (!$subscriptionEndDate) {
                $amount = $billingCycle === 'annual' ? $newPackage->annual_price : $newPackage->monthly_price;
                
                return [
                    'success' => true,
                    'amount' => $amount,
                    'is_prorated' => false
                ];
            }
            
            // Calculate the prorated amount
            $currentPrice = $billingCycle === 'annual' ? $currentPackage->annual_price : $currentPackage->monthly_price;
            $newPrice = $billingCycle === 'annual' ? $newPackage->annual_price : $newPackage->monthly_price;
            
            // Calculate the remaining days in the current subscription
            $now = now();
            $remainingDays = $now->diffInDays($subscriptionEndDate);
            $totalDays = $billingCycle === 'annual' ? 365 : 30;
            
            // Calculate the prorated amount
            $remainingAmount = ($currentPrice / $totalDays) * $remainingDays;
            $newAmount = $newPrice - $remainingAmount;
            
            // If downgrading, set amount to 0 (credit will be applied to next billing cycle)
            if ($newAmount < 0) {
                $newAmount = 0;
            }
            
            return [
                'success' => true,
                'amount' => round($newAmount, 2),
                'is_prorated' => true,
                'current_package' => [
                    'name' => $currentPackage->name,
                    'price' => $currentPrice
                ],
                'new_package' => [
                    'name' => $newPackage->name,
                    'price' => $newPrice
                ],
                'remaining_days' => $remainingDays,
                'total_days' => $totalDays,
                'remaining_amount' => round($remainingAmount, 2)
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating prorated amount: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'package_id' => $newPackage->id,
                'billing_cycle' => $billingCycle
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while calculating the prorated amount: ' . $e->getMessage()
            ];
        }
    }
}
