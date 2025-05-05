<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\OperatingHour;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class BusinessController extends Controller
{
    /**
     * Get a listing of businesses with optional filtering
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Business::with('user');
        
        // Apply category filter if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Apply district filter if provided
        if ($request->has('district')) {
            $query->where('district', $request->district);
        }
        
        // Only show approved businesses in the directory
        $query->where('status', 'approved');
        
        // Get businesses
        $businesses = $query->get();
        
        // Transform businesses to match the client's expected format
        $transformedBusinesses = $businesses->map(function ($business) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category,
                'district' => $business->district,
                'description' => $business->description,
                'package_type' => 'Basic', // Default to Basic tier for now
                'rating' => null, // No ratings yet
                'contact' => [
                    'phone' => $business->phone,
                    'email' => $business->user->email,
                    'website' => $business->website,
                    'address' => $business->address
                ],
                'image' => null // No images yet
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $transformedBusinesses
        ]);
    }

    /**
     * Get details for the authenticated user's business
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getBusinessDetails(Request $request): JsonResponse
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Find the business associated with the user
        $business = Business::where('user_id', $user->id)->with('operatingHours')->first();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        // Format operating hours
        $operatingHours = [];
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        // Initialize with default closed values
        foreach ($daysOfWeek as $day) {
            $operatingHours[$day] = 'Closed';
        }
        
        // Override with actual values from database
        foreach ($business->operatingHours as $hours) {
            if (!$hours->is_closed && $hours->opening_time && $hours->closing_time) {
                $operatingHours[$hours->day_of_week] = $hours->opening_time . ' - ' . $hours->closing_time;
            }
        }
        
        // Calculate average rating if reviews exist
        $averageRating = $business->reviews->count() > 0 
            ? $business->reviews->avg('rating') 
            : 0;
            
        // Transform business data to match client's expected format
        $businessData = [
            'id' => $business->id,
            'name' => $business->name,
            'category' => $business->category,
            'district' => $business->district,
            'description' => $business->description,
            'address' => $business->address,
            'phone' => $business->phone,
            'email' => $user->email,
            'website' => $business->website,
            'package_type' => $business->package_type ?? 'Basic',
            'adverts_remaining' => $business->adverts_remaining ?? 0,
            'rating' => $averageRating,
            'review_count' => $business->reviews->count(),
            'subscription' => [
                'status' => 'active',
                'next_billing_date' => date('Y-m-d', strtotime('+30 days')),
                'amount' => 0
            ],
            'statistics' => [
                'views' => $business->view_count ?? 0,
                'contacts' => $business->contact_count ?? 0,
                'reviews' => $business->reviews->count()
            ],
            'operatingHours' => $operatingHours
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $businessData
        ]);
    }

    /**
     * Update the authenticated user's business profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * Update the authenticated user's business profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBusinessProfile(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            // Find the business associated with the user
            $business = Business::where('user_id', $user->id)->first();
            
            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found. Please contact support if this issue persists.'
                ], 404);
            }
            
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'businessName' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'description' => 'required|string|min:50',
                'phone' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'website' => 'nullable|string|max:255',
                'address' => 'required|string|max:255',
                'operatingHours' => 'nullable|array',
            ], [
                'businessName.required' => 'Business name is required',
                'businessName.max' => 'Business name cannot exceed 255 characters',
                'category.required' => 'Please select a business category',
                'district.required' => 'Please select a district',
                'description.required' => 'Business description is required',
                'description.min' => 'Business description must be at least 50 characters',
                'phone.required' => 'Phone number is required',
                'email.required' => 'Email address is required',
                'email.email' => 'Please enter a valid email address',
                'address.required' => 'Business address is required',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please correct the errors in your form',
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
            
            // Check if email is already in use by another user
            if ($user->email !== $request->email) {
                $existingUser = User::where('email', $request->email)->where('id', '!=', $user->id)->first();
                if ($existingUser) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This email is already in use by another account',
                        'errors' => [
                            'email' => ['This email address is already registered. Please use a different email.']
                        ]
                    ], 422);
                }
            }
            
            // Update the business
            $business->name = $request->businessName;
            $business->category = $request->category;
            $business->district = $request->district;
            $business->description = $request->description;
            $business->phone = $request->phone;
            $business->website = $request->website;
            $business->address = $request->address;
            $business->save();
            
            // Update operating hours if provided
            if ($request->has('operatingHours') && is_array($request->operatingHours)) {
                foreach ($request->operatingHours as $day => $hours) {
                    // Skip if day is not valid
                    if (!in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])) {
                        continue;
                    }
                    
                    $isClosed = ($hours === 'Closed');
                    $openingTime = null;
                    $closingTime = null;
                    
                    // Parse hours if not closed
                    if (!$isClosed) {
                        $timeParts = explode(' - ', $hours);
                        if (count($timeParts) === 2) {
                            $openingTime = trim($timeParts[0]);
                            $closingTime = trim($timeParts[1]);
                        }
                    }
                    
                    // Update or create operating hours
                    OperatingHour::updateOrCreate(
                        ['business_id' => $business->id, 'day_of_week' => $day],
                        [
                            'opening_time' => $openingTime,
                            'closing_time' => $closingTime,
                            'is_closed' => $isClosed
                        ]
                    );
                }
            }
            
            // Update the user's email if it has changed
            if ($user->email !== $request->email) {
                $user->email = $request->email;
                $user->save();
            }
            
            // Get updated operating hours
            $business->load('operatingHours');
            $operatingHours = [];
            $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            // Initialize with default closed values
            foreach ($daysOfWeek as $day) {
                $operatingHours[$day] = 'Closed';
            }
            
            // Override with actual values from database
            foreach ($business->operatingHours as $hours) {
                if (!$hours->is_closed && $hours->opening_time && $hours->closing_time) {
                    $operatingHours[$hours->day_of_week] = $hours->opening_time . ' - ' . $hours->closing_time;
                }
            }
            
            // Return updated business data
            return response()->json([
                'status' => 'success',
                'message' => 'Business profile updated successfully',
                'data' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'category' => $business->category,
                    'district' => $business->district,
                    'description' => $business->description,
                    'address' => $business->address,
                    'phone' => $business->phone,
                    'email' => $user->email,
                    'website' => $business->website,
                    'operatingHours' => $operatingHours
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while updating your business profile',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    /**
     * Get a listing of businesses for the business directory
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $query = Business::with('user');
        
        // Apply category filter if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Apply district filter if provided
        if ($request->has('district')) {
            $query->where('district', $request->district);
        }
        
        // Only show approved businesses in the directory
        $query->where('status', 'approved');
        
        // Get businesses
        $businesses = $query->get();
        
        // Transform businesses to match the client's expected format
        $transformedBusinesses = $businesses->map(function ($business) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category,
                'district' => $business->district,
                'description' => $business->description,
                'package_type' => 'Basic', // Default to Basic tier for now
                'rating' => null, // No ratings yet
                'contact' => [
                    'phone' => $business->phone,
                    'email' => $business->user->email,
                    'website' => $business->website,
                    'address' => $business->address
                ],
                'image' => null // No images yet
            ];
        });
        
        return response()->json([
            'businesses' => $transformedBusinesses
        ]);
    }

    /**
     * Get a specific business by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            // Find the business by ID with basic relationships first
            $business = Business::with(['user', 'operatingHours', 'reviews'])->find($id);
            
            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }
            
            // Load products separately with error handling
            $products = [];
            try {
                $products = Product::where('business_id', $business->id)
                    ->where('status', 'active')
                    ->get();
            } catch (\Exception $e) {
                // Log product loading error but continue
                Log::error('Error loading products: ' . $e->getMessage());
            }
            
            // Calculate average rating if reviews exist
            $averageRating = $business->reviews->count() > 0 
                ? $business->reviews->avg('rating') 
                : null;
            
            // Format reviews if they exist
            $formattedReviews = $business->reviews->map(function ($review) {
                $reviewData = [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'reviewer_name' => $review->reviewer_name,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ];
                
                return $reviewData;
            })->values()->all();
            
            // Format operating hours
            $operatingHours = [];
            $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            // Initialize with default closed values
            foreach ($daysOfWeek as $day) {
                $operatingHours[$day] = 'Closed';
            }
            
            // Override with actual values from database
            foreach ($business->operatingHours as $hours) {
                if (!$hours->is_closed && $hours->opening_time && $hours->closing_time) {
                    $operatingHours[$hours->day_of_week] = $hours->opening_time . ' - ' . $hours->closing_time;
                }
            }
            
            // Transform business data to match client's expected format
            $businessData = [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category,
                'district' => $business->district,
                'description' => $business->description,
                'package_type' => $business->package_type ?? 'Basic', // Default to Basic tier if not set
                'rating' => $averageRating, // Use calculated average or null
                'review_count' => $business->review_count ?? $business->reviews->count(), // Add review count
                'contact' => [
                    'phone' => $business->phone,
                    'email' => $business->user->email,
                    'website' => $business->website,
                    'address' => $business->address,
                    'whatsapp' => $business->phone // Using phone as WhatsApp for now
                ],
                'hours' => $operatingHours,
                'products' => $products, // Use the separately loaded products
                'reviews' => $formattedReviews,
                'images' => []
            ];
            
            return response()->json($businessData);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching business details: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Increment the view count for a business
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function incrementViewCount($id): JsonResponse
    {
        try {
            $business = Business::findOrFail($id);
            $business->increment('view_count');
            
            return response()->json([
                'status' => 'success',
                'message' => 'View count incremented successfully',
                'view_count' => $business->view_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to increment view count: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Increment the contact count for a business
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function incrementContactCount($id): JsonResponse
    {
        try {
            $business = Business::findOrFail($id);
            $business->increment('contact_count');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Contact count incremented successfully',
                'contact_count' => $business->contact_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to increment contact count: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Remove all businesses from the database (admin use only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAllBusinesses(Request $request): JsonResponse
    {
        try {
            // Check for admin access - this is a destructive operation
            // In a production environment, you would want more robust authentication
            $user = Auth::user();
            if (!$user || $user->email !== config('app.admin_email')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            // Get count before deletion
            $count = Business::count();
            
            // Delete all operating hours first (respecting foreign key constraints)
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Delete all businesses
            Business::truncate();
            
            // Reset auto-increment
            DB::statement('ALTER TABLE businesses AUTO_INCREMENT = 1;');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            return response()->json([
                'status' => 'success',
                'message' => $count . ' businesses have been removed from the directory.',
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while removing businesses: ' . $e->getMessage()
            ], 500);
        }
    }
}
