<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get all approved reviews
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $reviews = Review::where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'business_id' => $review->business_id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'reviewer_name' => $review->reviewer_name,
                        'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            
            return response()->json([
                'status' => 'success',
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get reviews: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a new review
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'business_id' => 'required|exists:businesses,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string',
                'reviewer_name' => 'required|string|max:100',
                'reviewer_email' => 'nullable|email|max:100'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Create the review
            $review = Review::create([
                'business_id' => $request->business_id,
                'user_id' => null, // No user association
                'rating' => $request->rating,
                'comment' => $request->comment,
                'reviewer_name' => $request->reviewer_name,
                'reviewer_email' => $request->reviewer_email,
                'is_approved' => true // Auto-approve for now
            ]);
            
            // Update the business review count
            $business = Business::find($request->business_id);
            $business->increment('review_count');
            
            // Format the review for the response
            $formattedReview = [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewer_name' => $review->reviewer_name,
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
            ];
            
            // Update the business average rating
            $allReviews = Review::where('business_id', $business->id)->get();
            $averageRating = $allReviews->avg('rating');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Review submitted successfully',
                'data' => $formattedReview
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit review: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get reviews for a business
     * 
     * @param int $businessId
     * @return JsonResponse
     */
    public function getBusinessReviews(int $businessId): JsonResponse
    {
        try {
            $business = Business::findOrFail($businessId);
            
            $reviews = Review::where('business_id', $businessId)
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'reviewer_name' => $review->reviewer_name,
                        'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'average_rating' => $reviews->avg('rating') ?: 0,
                    'review_count' => $reviews->count(),
                    'reviews' => $reviews
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get reviews: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a new anonymous review
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeAnonymousReview(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'business_id' => 'required|exists:businesses,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string',
                'reviewer_name' => 'required|string|max:100',
                'reviewer_email' => 'nullable|email|max:100'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Create the review with pending approval
            $review = Review::create([
                'business_id' => $request->business_id,
                'user_id' => null, // No user association for anonymous reviews
                'rating' => $request->rating,
                'comment' => $request->comment,
                'reviewer_name' => $request->reviewer_name,
                'reviewer_email' => $request->reviewer_email,
                'is_approved' => false // Anonymous reviews require approval
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Thank you for your review. It has been submitted for approval.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit review: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}