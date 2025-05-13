<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Business;

class ReviewController extends Controller
{
    public function getPendingReviews()
    {
        $reviews = Review::where('status', 'pending')
            ->with('business:id,name')
            ->paginate(10); // Limit to 10 reviews per page

        $formattedReviews = $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'business_id' => $review->business_id,
                'business_name' => $review->business->name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewer_name' => $review->reviewer_name,
                'reviewer_email' => $review->reviewer_email,
                'status' => $review->status,
                'created_at' => $review->created_at->format('Y-m-d H:i:s')
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $formattedReviews,
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
            ],
        ]);
    }
    
    public function approveReview($id)
    {
        $review = Review::find($id);
        
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found'
            ], 404);
        }
        
        $review->status = 'approved';
        $review->save();
        
        // Update business review count
        $business = Business::find($review->business_id);
        if ($business) {
            $approvedReviews = Review::where('business_id', $business->id)
                ->where('status', 'approved')
                ->count();
                
            $business->review_count = $approvedReviews;
            $business->save();
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Review approved successfully'
        ]);
    }
    
    public function rejectReview(Request $request, $id)
    {
        $review = Review::find($id);
        
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found'
            ], 404);
        }
        
        $review->status = 'rejected';
        $review->rejection_reason = $request->input('reason');
        $review->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Review rejected successfully'
        ]);
    }
}