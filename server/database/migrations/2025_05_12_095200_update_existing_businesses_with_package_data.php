<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Business;
use App\Models\Package;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Log::info('Running migration to update existing businesses with package data');
        
        // Get all packages
        $packages = Package::all();
        
        if ($packages->isEmpty()) {
            Log::warning('No packages found in the database. Make sure to run the package seeder first.');
            return;
        }
        
        // Get package mapping
        $packageMap = [];
        foreach ($packages as $package) {
            $packageMap[$package->name] = $package->id;
        }
        
        Log::info('Found packages', ['packages' => $packageMap]);
        
        // Get all businesses
        $businesses = Business::all();
        
        if ($businesses->isEmpty()) {
            Log::warning('No businesses found in the database');
            return;
        }
        
        Log::info('Found ' . $businesses->count() . ' businesses to update');
        
        // First, make sure the user with email sthwaloe@gmail.com has a business
        $user = \App\Models\User::where('email', 'sthwaloe@gmail.com')->first();
        if ($user) {
            // Check if user already has a business
            $userBusiness = Business::where('user_id', $user->id)->first();
            
            if (!$userBusiness) {
                // Find any business that could be linked to this user
                $anyBusiness = $businesses->first();
                if ($anyBusiness) {
                    $anyBusiness->user_id = $user->id;
                    $anyBusiness->save();
                    
                    Log::info('Linked existing business to user', [
                        'user_id' => $user->id,
                        'business_id' => $anyBusiness->id,
                        'business_name' => $anyBusiness->name
                    ]);
                }
            } else {
                Log::info('User already has a business', [
                    'user_id' => $user->id,
                    'business_id' => $userBusiness->id
                ]);
            }
        }
        
        // Update each business with the correct package_id based on package_type
        foreach ($businesses as $business) {
            $packageType = $business->package_type ?? 'Basic';
            
            // If package_type exists in our mapping, update the package_id
            if (isset($packageMap[$packageType])) {
                $business->package_id = $packageMap[$packageType];
                
                // Set default values for package-related fields if they're null
                if ($business->billing_cycle === null) {
                    $business->billing_cycle = 'monthly';
                }
                
                if ($business->subscription_ends_at === null) {
                    $business->subscription_ends_at = now()->addDays(30);
                }
                
                // Set adverts_remaining based on package
                $package = $packages->firstWhere('id', $business->package_id);
                if ($package && $business->adverts_remaining === null) {
                    $business->adverts_remaining = $package->advert_limit;
                }
                
                // Set social_features_remaining based on package type
                if ($business->social_features_remaining === null) {
                    if ($packageType === 'Gold') {
                        $business->social_features_remaining = 2;
                    } elseif ($packageType === 'Silver') {
                        $business->social_features_remaining = 1;
                    } else {
                        $business->social_features_remaining = 0;
                    }
                }
                
                $business->save();
                
                Log::info('Updated business with package data', [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'package_type' => $packageType,
                    'package_id' => $business->package_id
                ]);
            } else {
                Log::warning('Package type not found in mapping', [
                    'business_id' => $business->id,
                    'package_type' => $packageType
                ]);
            }
        }
        
        Log::info('Migration completed successfully');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only updates data, so there's no need for a down method
    }
};
