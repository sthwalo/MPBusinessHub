<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SocialMediaController extends Controller
{
    /**
     * Update social media links for a business
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'facebook' => 'nullable|url',
                'instagram' => 'nullable|url',
                'twitter' => 'nullable|url',
                'linkedin' => 'nullable|url',
                'youtube' => 'nullable|url',
                'tiktok' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $business = Business::where('user_id', $user->id)->first();

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }

            // Check if business has access to social links feature
            if (!in_array($business->package_type, ['Bronze', 'Silver', 'Gold'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your current package does not include social media links. Please upgrade to Bronze or higher.'
                ], 403);
            }

            // Update social media links
            $business->social_media = [
                'facebook' => $request->facebook,
                'instagram' => $request->instagram,
                'twitter' => $request->twitter,
                'linkedin' => $request->linkedin,
                'youtube' => $request->youtube,
                'tiktok' => $request->tiktok,
            ];
            
            $business->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Social media links updated successfully',
                'data' => $business->social_media
            ]);
        } catch (\Exception $e) {
            Log::error('Social media update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating social media links'
            ], 500);
        }
    }

    /**
     * Create a social media feature post (Silver and Gold tier only)
     */
    public function createFeaturePost(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'platform' => 'required|string|in:facebook,instagram,twitter,linkedin',
                'content' => 'required|string|max:500',
                'image' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $business = Business::where('user_id', $user->id)->first();

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }

            // Check if business has Silver or Gold package
            if (!in_array($business->package_type, ['Silver', 'Gold'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This feature is only available for Silver and Gold package subscribers'
                ], 403);
            }

            // Check if business has remaining social features
            if ($business->package_type === 'Silver' && $business->social_features_remaining <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have used all your social media features for this month'
                ], 403);
            }

            if ($business->package_type === 'Gold' && $business->social_features_remaining <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have used all your social media features for this month'
                ], 403);
            }

            // In a real implementation, you would:
            // 1. Store the post content
            // 2. Schedule it for posting
            // 3. Integrate with social media APIs

            // For now, just decrement the counter
            $business->social_features_remaining -= 1;
            $business->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Social media feature post created successfully',
                'remaining_features' => $business->social_features_remaining
            ]);
        } catch (\Exception $e) {
            Log::error('Social media feature post creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating social media feature post'
            ], 500);
        }
    }
}