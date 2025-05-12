<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Log;

class BusinessSearchService
{
    /**
     * Get a filtered list of businesses
     *
     * @param array $filters
     * @return array
     */
    public function getFilteredBusinesses(array $filters = [])
    {
        try {
            $query = Business::with('user');
            
            // Apply category filter if provided
            if (isset($filters['category']) && !empty($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            // Apply district filter if provided
            if (isset($filters['district']) && !empty($filters['district'])) {
                $query->where('district', $filters['district']);
            }
            
            // Apply name search if provided
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->where('name', 'like', '%' . $filters['search'] . '%');
            }
            
            // Apply status filter (default to approved businesses only)
            $status = $filters['status'] ?? 'approved';
            $query->where('status', $status);
            
            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'name';
            $sortOrder = $filters['sort_order'] ?? 'asc';
            $query->orderBy($sortBy, $sortOrder);
            
            // Apply pagination
            $perPage = $filters['per_page'] ?? 10;
            $page = $filters['page'] ?? 1;
            
            $businesses = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Transform businesses to match the client's expected format
            $transformedBusinesses = $businesses->map(function ($business) {
                return [
                    'id' => $business->id,
                    'name' => $business->name,
                    'category' => $business->category,
                    'district' => $business->district,
                    'description' => $business->description,
                    'package_type' => $business->package_type ?? 'Basic',
                    'adverts_remaining' => $business->adverts_remaining ?? 0,
                    'social_features_remaining' => $business->social_features_remaining ?? 0,
                    'rating' => $this->calculateAverageRating($business),
                    'contact' => [
                        'phone' => $business->phone,
                        'email' => $business->user->email,
                        'website' => $business->website,
                        'address' => $business->address
                    ],
                    'image_url' => $business->image_url
                ];
            });
            
            return [
                'success' => true,
                'data' => $transformedBusinesses,
                'pagination' => [
                    'total' => $businesses->total(),
                    'per_page' => $businesses->perPage(),
                    'current_page' => $businesses->currentPage(),
                    'last_page' => $businesses->lastPage()
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting filtered businesses: ' . $e->getMessage(), [
                'filters' => $filters
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving businesses: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get business categories
     *
     * @return array
     */
    public function getCategories()
    {
        try {
            $categories = Business::where('status', 'approved')
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values();
            
            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (\Exception $e) {
            Log::error('Error getting business categories: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving categories: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get business districts
     *
     * @return array
     */
    public function getDistricts()
    {
        try {
            $districts = Business::where('status', 'approved')
                ->distinct()
                ->pluck('district')
                ->filter()
                ->values();
            
            return [
                'success' => true,
                'data' => $districts
            ];
        } catch (\Exception $e) {
            Log::error('Error getting business districts: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving districts: ' . $e->getMessage()
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
        // Load reviews if not already loaded
        if (!$business->relationLoaded('reviews')) {
            $business->load('reviews');
        }
        
        $reviews = $business->reviews;
        $reviewCount = count($reviews);
        
        if ($reviewCount === 0) {
            return 0;
        }
        
        $totalRating = $reviews->sum('rating');
        return round($totalRating / $reviewCount, 1);
    }
}
