<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\AdvertService;

class AdvertController extends Controller
{
    protected $advertService;

    public function __construct(AdvertService $advertService)
    {
        $this->advertService = $advertService;
    }
    /**
     * Get all adverts for the authenticated user's business
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }

        // Check and reset monthly count if needed
        $this->advertService->checkAndResetMonthlyCount($business);
        
        // Check if business has adverts remaining
        if ($business->adverts_remaining <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No adverts remaining. Please upgrade your package.'
            ], 403);
        }
        
        $adverts = Advert::where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $adverts
        ]);
    }
    
    /**
     * Create a new advert
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'status' => 'required|in:active,scheduled',
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
        
        // Check if business has adverts remaining
        if ($business->adverts_remaining <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No adverts remaining. Please upgrade your package.'
            ], 403);
        }
        
        // Create the advert
        $advert = Advert::create([
            'business_id' => $business->id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'status' => new \DateTime($request->startDate) <= new \DateTime() ? 'active' : 'scheduled',
        ]);
        
        // Decrement adverts remaining
        $business->decrement('adverts_remaining');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Advert created successfully',
            'data' => $advert
        ], 201);
    }
    
    /**
     * Delete an advert
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
        
        $advert = Advert::where('id', $id)
            ->where('business_id', $business->id)
            ->first();
            
        if (!$advert) {
            return response()->json([
                'status' => 'error',
                'message' => 'Advert not found'
            ], 404);
        }
        
        $advert->delete();

         // Increment adverts remaining when deleting
         $this->advertService->incrementAdvertCount($business);
        
        
        return response()->json([
            'status' => 'success',
            'message' => 'Advert deleted successfully'
        ]);
    }
    public function getActiveAdverts(): JsonResponse
    {
        $activeAdverts = Advert::with('business')
            ->whereHas('business', function($query) {
                $query->where('status', 'approved');
            })
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $activeAdverts
        ]);
    }
}