<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use App\Models\OperatingHour;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BusinessService
{
    /**
     * Get a business by ID
     *
     * @param int $id
     * @return Business|null
     */
    public function getBusinessById($id)
    {
        return Business::find($id);
    }
    
    /**
     * Get a business by user ID
     *
     * @param int $userId
     * @return Business|null
     */
    public function getBusinessByUserId($userId)
    {
        return Business::where('user_id', $userId)->first();
    }
    
    /**
     * Get the authenticated user's business
     *
     * @return Business|null
     */
    public function getCurrentUserBusiness()
    {
        $user = Auth::user();
        return $user ? $this->getBusinessByUserId($user->id) : null;
    }
    
    /**
     * Get detailed business information including related data
     *
     * @param int $businessId
     * @return array
     */
    public function getBusinessDetails($businessId)
    {
        try {
            // Find the business with related data
            $business = Business::with(['operatingHours', 'package', 'reviews', 'products'])
                ->find($businessId);
            
            if (!$business) {
                return [
                    'success' => false,
                    'message' => 'Business not found'
                ];
            }
            
            // Format operating hours
            $operatingHours = $this->formatOperatingHours($business->operatingHours);
            
            // Calculate average rating
            $averageRating = 0;
            $reviewCount = count($business->reviews);
            
            if ($reviewCount > 0) {
                $totalRating = $business->reviews->sum('rating');
                $averageRating = $totalRating / $reviewCount;
            }
            
            // Format reviews
            $formattedReviews = $this->formatReviews($business->reviews);
            
            // Format products
            $products = $this->formatProducts($business->products);
            
            // Build the business data array
            $businessData = [
                'success' => true,
                'data' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'category' => $business->category,
                    'district' => $business->district,
                    'description' => $business->description,
                    'package_type' => $business->package ? $business->package->type : 'Basic',
                    'package_name' => $business->package ? $business->package->name : 'Basic',
                    'status' => $business->status,
                    'adverts_remaining' => $business->adverts_remaining ?? 0,
                    'social_features_remaining' => $business->social_features_remaining ?? 0,
                    'rating' => round($averageRating, 1),
                    'review_count' => $reviewCount,
                    'view_count' => $business->view_count ?? 0,
                    'contact_count' => $business->contact_count ?? 0,
                    'contact' => [
                        'phone' => $business->phone,
                        'email' => $business->user->email,
                        'website' => $business->website,
                        'address' => $business->address,
                        'whatsapp' => $business->phone, // Using phone as WhatsApp for now
                        'social_media' => $business->social_media ?? []
                    ],
                    'hours' => $operatingHours,
                    'products' => $products,
                    'reviews' => $formattedReviews,
                    'images' => []
                ]
            ];
            
            return $businessData;
            
        } catch (\Exception $e) {
            Log::error('Error getting business details: ' . $e->getMessage(), [
                'business_id' => $businessId
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while fetching business details: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format operating hours
     *
     * @param Collection $operatingHours
     * @return array
     */
    protected function formatOperatingHours($operatingHours)
    {
        $formattedHours = [];
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        // Initialize with default closed values
        foreach ($daysOfWeek as $day) {
            $formattedHours[$day] = 'Closed';
        }
        
        // Override with actual values from database
        foreach ($operatingHours as $hours) {
            if (!$hours->is_closed && $hours->opening_time && $hours->closing_time) {
                $formattedHours[$hours->day_of_week] = $hours->opening_time . ' - ' . $hours->closing_time;
            }
        }
        
        return $formattedHours;
    }
    
    /**
     * Format reviews
     *
     * @param Collection $reviews
     * @return array
     */
    protected function formatReviews($reviews)
    {
        return $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user_name' => $review->user_name ?? 'Anonymous',
                'created_at' => $review->created_at->format('Y-m-d H:i:s')
            ];
        })->toArray();
    }
    
    /**
     * Format products
     *
     * @param Collection $products
     * @return array
     */
    protected function formatProducts($products)
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'status' => $product->status,
                'image_url' => $product->image_url
            ];
        })->toArray();
    }
    
    /**
     * Remove all businesses (admin function)
     *
     * @return array
     */
    public function removeAllBusinesses()
    {
        try {
            // Get count before deletion
            $count = Business::count();
            
            // Delete all operating hours first (respecting foreign key constraints)
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Delete all businesses
            Business::truncate();
            
            // Reset auto-increment
            DB::statement('ALTER TABLE businesses AUTO_INCREMENT = 1;');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            return [
                'success' => true,
                'message' => $count . ' businesses have been removed from the directory.',
                'count' => $count
            ];
        } catch (\Exception $e) {
            Log::error('Error removing all businesses: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred while removing businesses: ' . $e->getMessage()
            ];
        }
    }
}
