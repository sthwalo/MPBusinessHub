<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
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
                'comment' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Get the authenticated user
            $user = Auth::user();
            
            // Check if user has already reviewed this business
            $existingReview = Review::where('user_id', $user->id)
                ->where('business_id', $request->business_id)
                ->first();
                
            if ($existingReview) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already reviewed this business'
                ], 409);
            }
            
            // Create the review
            $review = Review::create([
                'business_id' => $request->business_id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_approved' => true // Auto-approve for now
            ]);
            
            // Load the user relationship for the response
            $review->load('user');
            
            // Format the review for the response
            $formattedReview = [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                ]
            ];
            
            // Update the business average rating
            $business = Business::find($request->business_id);
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
}