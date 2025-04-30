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
     * Store a newly created review in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get authenticated user
            $user = Auth::user();
            
            // Check if user has already reviewed this business
            $existingReview = Review::where('user_id', $user->id)
                ->where('business_id', $request->business_id)
                ->first();
                
            if ($existingReview) {
                // Update existing review
                $existingReview->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);
                
                $review = $existingReview;
                $message = 'Review updated successfully';
            } else {
                // Create new review
                $review = Review::create([
                    'business_id' => $request->business_id,
                    'user_id' => $user->id,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);
                
                $message = 'Review submitted successfully';
            }
            
            // Load user relationship
            $review->load('user');
            
            // Format review for response
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
            
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $formattedReview
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while submitting review: ' . $e->getMessage()
            ], 500);
        }
    }
}