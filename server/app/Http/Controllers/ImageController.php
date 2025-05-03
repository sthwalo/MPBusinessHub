<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Business;

class ImageController extends Controller
{
    /**
     * Upload business profile image
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadBusinessImage(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide a valid image file',
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
            
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
            
            // Handle file upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $business->id . '.' . $image->getClientOriginalExtension();
                
                // Store the file in the public disk under the business_images folder
                $path = $image->storeAs('business_images', $filename, 'public');
                
                // Update the business image_url
                $business->image_url = '/storage/' . $path;
                $business->save();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Image uploaded successfully',
                    'image_url' => $business->image_url
                ]);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'No image file provided'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload product image
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadProductImage(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'product_id' => 'required|integer|exists:products,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide a valid image file and product ID',
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
            
            // Get the product
            $product = \App\Models\Product::find($request->product_id);
            
            // Verify ownership
            if ($product->business->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to upload images for this product'
                ], 403);
            }
            
            // Handle file upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_product_' . $product->id . '.' . $image->getClientOriginalExtension();
                
                // Store the file in the public disk under the product_images folder
                $path = $image->storeAs('product_images', $filename, 'public');
                
                // Update the product image_url
                $product->image_url = '/storage/' . $path;
                $product->save();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Product image uploaded successfully',
                    'image_url' => $product->image_url
                ]);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'No image file provided'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload product image: ' . $e->getMessage()
            ], 500);
        }
    }
}