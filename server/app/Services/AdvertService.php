<?php

namespace App\Services;

use App\Models\Business;
use Carbon\Carbon;

class AdvertService
{
    /**
     * Check if the monthly advert count needs to be reset and do so if needed
     *
     * @param Business $business
     * @return void
     */
    public function checkAndResetMonthlyCount(Business $business): void
    {
        // If last_adverts_reset is null or it's a new month since last reset
        if (!$business->last_adverts_reset || 
            Carbon::parse($business->last_adverts_reset)->month !== Carbon::now()->month ||
            Carbon::parse($business->last_adverts_reset)->year !== Carbon::now()->year) {
            
            // Reset adverts based on package type
            switch ($business->package_type) {
                case 'Gold':
                    $business->adverts_remaining = 4;
                    break;
                case 'Silver':
                    $business->adverts_remaining = 2;
                    break;
                case 'Bronze':
                    $business->adverts_remaining = 1;
                    break;
                default: // Free or any other package
                    $business->adverts_remaining = 0;
            }
            
            $business->last_adverts_reset = Carbon::now();
            $business->save();
        }
    }
    
    /**
     * Increment the adverts_remaining count when an advert is deleted
     *
     * @param Business $business
     * @return void
     */
    public function incrementAdvertCount(Business $business): void
    {
        // Get the maximum adverts for this package type
        $maxAdverts = 0;
        switch ($business->package_type) {
            case 'Gold':
                $maxAdverts = 4;
                break;
            case 'Silver':
                $maxAdverts = 2;
                break;
            case 'Bronze':
                $maxAdverts = 1;
                break;
        }
        
        // Only increment if below the maximum
        if ($business->adverts_remaining < $maxAdverts) {
            $business->increment('adverts_remaining');
        }
    }
}