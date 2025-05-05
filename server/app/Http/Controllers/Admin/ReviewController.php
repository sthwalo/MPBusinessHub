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
        $reviews = Review::where('is_approved', false)
            ->with('business:id,name')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'business_id' => $review->business_id,
                    'business_name' => $review->business->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'reviewer_name' => $review->reviewer_name,
                    'reviewer_email' => $review->reviewer_email,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s')
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => $reviews
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
        
        $review->is_approved = true;
        $review->save();
        
        // Update business review count
        $business = Business::find($review->business_id);
        if ($business) {
            $approvedReviews = Review::where('business_id', $business->id)
                ->where('is_approved', true)
                ->count();
                
            $business->review_count = $approvedReviews;
            $business->save();
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Review approved successfully'
        ]);
    }
    
    public function rejectReview($id)
    {
        $review = Review::find($id);
        
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found'
            ], 404);
        }
        
        $review->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Review rejected and deleted successfully'
        ]);
    }
}