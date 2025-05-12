<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Log;

class BusinessStatisticsService
{
    /**
     * Increment the view count for a business
     *
     * @param int $businessId
     * @return array
     */
    public function incrementViewCount($businessId)
    {
        try {
            $business = Business::findOrFail($businessId);
            $business->increment('view_count');
            
            return [
                'success' => true,
                'message' => 'View count incremented successfully',
                'view_count' => $business->view_count
            ];
        } catch (\Exception $e) {
            Log::error('Failed to increment view count: ' . $e->getMessage(), [
                'business_id' => $businessId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to increment view count: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Increment the contact count for a business
     *
     * @param int $businessId
     * @return array
     */
    public function incrementContactCount($businessId)
    {
        try {
            $business = Business::findOrFail($businessId);
            $business->increment('contact_count');
            
            return [
                'success' => true,
                'message' => 'Contact count incremented successfully',
                'contact_count' => $business->contact_count
            ];
        } catch (\Exception $e) {
            Log::error('Failed to increment contact count: ' . $e->getMessage(), [
                'business_id' => $businessId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to increment contact count: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get statistics for a business
     *
     * @param int $businessId
     * @return array
     */
    public function getBusinessStatistics($businessId)
    {
        try {
            $business = Business::findOrFail($businessId);
            
            return [
                'success' => true,
                'data' => [
                    'view_count' => $business->view_count ?? 0,
                    'contact_count' => $business->contact_count ?? 0,
                    'product_count' => $business->products()->count(),
                    'review_count' => $business->reviews()->count(),
                    'average_rating' => $this->calculateAverageRating($business)
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get business statistics: ' . $e->getMessage(), [
                'business_id' => $businessId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get business statistics: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate the average rating for a business
     *
     * @param Business $business
     * @return float
     */
    protected function calculateAverageRating(Business $business)
    {
        $reviews = $business->reviews;
        $reviewCount = count($reviews);
        
        if ($reviewCount === 0) {
            return 0;
        }
        
        $totalRating = $reviews->sum('rating');
        return round($totalRating / $reviewCount, 1);
    }
}
