<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Business;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageService
{
    /**
     * Get all available packages
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPackages()
    {
        // Get all active packages
        $packages = Package::where('is_active', true)->get();
        
        // If no active packages, get all packages regardless of status
        if ($packages->isEmpty()) {
            $packages = Package::all();
        }
        
        return $packages;
    }

    /**
     * Get a specific package by ID
     *
     * @param int $packageId
     * @return Package|null
     */
    public function getPackageById($packageId)
    {
        return Package::find($packageId);
    }

    /**
     * Get a specific package by name
     *
     * @param string $packageName
     * @return Package|null
     */
    public function getPackageByName($packageName)
    {
        return Package::where('name', $packageName)->first();
    }

    /**
     * Compare two packages to determine if it's an upgrade, downgrade, or same level
     *
     * @param string|int $currentPackage - Current package ID or name
     * @param string|int $newPackage - New package ID or name
     * @return string - 'upgrade', 'downgrade', or 'same'
     */
    public function comparePackages($currentPackage, $newPackage)
    {
        // Define the package hierarchy (lower index = lower tier)
        $packageHierarchy = ['Free', 'Bronze', 'Silver', 'Gold'];
        
        // Get package names if IDs were provided
        if (is_numeric($currentPackage)) {
            $currentPackageObj = $this->getPackageById($currentPackage);
            $currentPackage = $currentPackageObj ? $currentPackageObj->name : null;
        }
        
        if (is_numeric($newPackage)) {
            $newPackageObj = $this->getPackageById($newPackage);
            $newPackage = $newPackageObj ? $newPackageObj->name : null;
        }
        
        // If either package is not found, return null
        if (!$currentPackage || !$newPackage) {
            return null;
        }
        
        $currentIndex = array_search($currentPackage, $packageHierarchy);
        $newIndex = array_search($newPackage, $packageHierarchy);
        
        // If either package is not in the hierarchy, return null
        if ($currentIndex === false || $newIndex === false) {
            return null;
        }
        
        if ($newIndex > $currentIndex) {
            return 'upgrade';
        } elseif ($newIndex < $currentIndex) {
            return 'downgrade';
        } else {
            return 'same';
        }
    }

    /**
     * Calculate the price for a package based on billing cycle
     *
     * @param Package $package
     * @param string $billingCycle - 'monthly' or 'annual'
     * @return float
     */
    public function calculatePackagePrice(Package $package, $billingCycle = 'monthly')
    {
        return $billingCycle === 'annual' ? $package->price_annual : $package->price_monthly;
    }

    /**
     * Calculate prorated amount when upgrading/downgrading
     *
     * @param Business $business
     * @param Package $newPackage
     * @param string $newBillingCycle
     * @return float
     */
    public function calculateProratedAmount(Business $business, Package $newPackage, $newBillingCycle = 'monthly')
    {
        // Get current subscription details
        $currentPackage = $this->getPackageById($business->package_id);
        $currentBillingCycle = $business->billing_cycle ?? 'monthly';
        $subscriptionEndsAt = $business->subscription_ends_at;
        
        // If no current package or subscription end date, return full price
        if (!$currentPackage || !$subscriptionEndsAt) {
            return $this->calculatePackagePrice($newPackage, $newBillingCycle);
        }
        
        // Calculate days remaining in current subscription
        $now = now();
        $daysRemaining = $now->diffInDays($subscriptionEndsAt);
        
        // If subscription already expired, return full price
        if ($daysRemaining <= 0) {
            return $this->calculatePackagePrice($newPackage, $newBillingCycle);
        }
        
        // Calculate total days in billing cycle
        $totalDays = $currentBillingCycle === 'annual' ? 365 : 30;
        
        // Calculate remaining value of current subscription
        $currentPrice = $this->calculatePackagePrice($currentPackage, $currentBillingCycle);
        $remainingValue = ($daysRemaining / $totalDays) * $currentPrice;
        
        // Calculate new subscription price
        $newPrice = $this->calculatePackagePrice($newPackage, $newBillingCycle);
        
        // If downgrading, credit the remaining value
        $changeType = $this->comparePackages($currentPackage->id, $newPackage->id);
        if ($changeType === 'downgrade') {
            return max(0, $newPrice - $remainingValue);
        }
        
        // If upgrading, charge the difference plus the new subscription
        if ($changeType === 'upgrade') {
            return $newPrice - $remainingValue;
        }
        
        // If same package but different billing cycle
        return $newPrice;
    }
}
