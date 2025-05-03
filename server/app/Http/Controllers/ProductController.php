<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Get all products for a business
     */
    public function index(Request $request): JsonResponse
    {
        // If business_id is provided, get products for that business
        if ($request->has('business_id')) {
            $products = Product::where('business_id', $request->business_id)
                ->where('status', 'active')
                ->get();
                
            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        }
        
        // Otherwise, get products for the authenticated user's business
        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        $products = Product::where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
    
    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
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
        
        // Check if business package allows products
        if (!in_array($business->package_type, ['Silver', 'Gold'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your package does not support adding products. Please upgrade to Silver or Gold.'
            ], 403);
        }
        
        // Create the product
        $product = Product::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_url' => $request->image_url,
            'is_featured' => $request->is_featured ?? false,
            'status' => 'active',
        ]);
        
        // Log product creation
        Log::info('Product created', [
            'product_id' => $product->id,
            'business_id' => $business->id,
            'name' => $product->name
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }
    
    /**
     * Get a specific product
     */
    public function show($id): JsonResponse
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
    
    /**
     * Update a product
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'status' => 'sometimes|in:active,inactive',
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
        
        $product = Product::where('id', $id)
            ->where('business_id', $business->id)
            ->first();
            
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }
        
        $product->update($request->only([
            'name',
            'description',
            'price',
            'image_url',
            'is_featured',
            'status',
        ]));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }
    
    /**
     * Delete a product
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        $product = Product::where('id', $id)
            ->where('business_id', $business->id)
            ->first();
            
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }
        
        $product->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ]);
    }
}