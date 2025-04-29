<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
        $business = Business::where('user_id', $user->id)->first();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
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
            'package_type' => 'Basic', // Default to Basic tier for now
            'adverts_remaining' => 0,
            'subscription' => [
                'status' => 'active',
                'next_billing_date' => date('Y-m-d', strtotime('+30 days')),
                'amount' => 0
            ],
            'statistics' => [
                'views' => 0,
                'contacts' => 0,
                'reviews' => 0
            ],
            'operatingHours' => [
                'monday' => '8:00 - 18:00',
                'tuesday' => '8:00 - 18:00',
                'wednesday' => '8:00 - 18:00',
                'thursday' => '8:00 - 18:00',
                'friday' => '8:00 - 18:00',
                'saturday' => '9:00 - 17:00',
                'sunday' => '9:00 - 16:00'
            ]
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
            
            // Update the user's email if it has changed
            if ($user->email !== $request->email) {
                $user->email = $request->email;
                $user->save();
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
                    'operatingHours' => $request->operatingHours ?? null
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
            // Find the business by ID
            $business = Business::with('user')->find($id);
            
            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }
            
            // Transform business data to match client's expected format
            $businessData = [
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
                    'address' => $business->address,
                    'whatsapp' => $business->phone // Using phone as WhatsApp for now
                ],
                'hours' => [
                    'monday' => '8:00 - 17:00',
                    'tuesday' => '8:00 - 17:00',
                    'wednesday' => '8:00 - 17:00',
                    'thursday' => '8:00 - 17:00',
                    'friday' => '8:00 - 17:00',
                    'saturday' => '9:00 - 14:00',
                    'sunday' => 'Closed'
                ],
                'products' => [],
                'reviews' => [],
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
}
