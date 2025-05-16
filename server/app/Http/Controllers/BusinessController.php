<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\BusinessService;
use App\Services\BusinessProfileService;
use App\Services\BusinessStatisticsService;
use App\Services\BusinessSearchService;
use App\Services\BusinessPlanService;
use Illuminate\Support\Facades\Log;

class BusinessController extends Controller
{
    protected $businessService;
    protected $profileService;
    protected $statisticsService;
    protected $searchService;
    protected $planService;
    
    /**
     * Constructor
     */
    public function __construct(
        BusinessService $businessService,
        BusinessProfileService $profileService,
        BusinessStatisticsService $statisticsService,
        BusinessSearchService $searchService,
        BusinessPlanService $planService
    ) {
        $this->businessService = $businessService;
        $this->profileService = $profileService;
        $this->statisticsService = $statisticsService;
        $this->searchService = $searchService;
        $this->planService = $planService;
    }
    
    /**
     * Get a listing of businesses with optional filtering
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $result = $this->searchService->getFilteredBusinesses($filters);
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $result['data'],
            'pagination' => $result['pagination'] ?? null
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
        $business = $this->businessService->getCurrentUserBusiness();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        $result = $this->businessService->getBusinessDetails($business->id);
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        // Return the data in the format expected by the frontend
        return response()->json([
            'status' => 'success',
            'data' => $result['data']
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
        $business = $this->businessService->getCurrentUserBusiness();
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        // Validate the request data
        $validationResult = $this->profileService->validateProfileData($request->all());
        
        if (!$validationResult['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $validationResult['message'],
                'errors' => $validationResult['errors']
            ], 422);
        }
        
        // Update the business profile
        $updateResult = $this->profileService->updateProfile($business, $validationResult['data']);
        
        if (!$updateResult['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $updateResult['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Business profile updated successfully',
            'data' => $updateResult['business']
        ]);
    }

    /**
     * Get a listing of businesses for the business directory
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['status'] = 'approved'; // Only show approved businesses
        
        $result = $this->searchService->getFilteredBusinesses($filters);
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $result['data']
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
        $result = $this->businessService->getBusinessDetails($id);
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 404);
        }
        
        return response()->json($result['data']);
    }

    /**
     * Increment the view count for a business
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function incrementViewCount($id): JsonResponse
    {
        $result = $this->statisticsService->incrementViewCount($id);
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'View count incremented successfully',
            'view_count' => $result['view_count']
        ]);
    }
    
    /**
     * Increment the contact count for a business
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function incrementContactCount($id): JsonResponse
    {
        $result = $this->statisticsService->incrementContactCount($id);
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Contact count incremented successfully',
            'contact_count' => $result['contact_count']
        ]);
    }
    
    /**
     * Remove all businesses from the database (admin use only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAllBusinesses(Request $request): JsonResponse
    {
        // Check for admin access - this is a destructive operation
        $user = Auth::user();
        if (!$user || $user->email !== config('app.admin_email')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $result = $this->businessService->removeAllBusinesses();
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
            'data' => [
                'count' => $result['count']
            ]
        ]);
    }
    
    /**
     * Upgrade a business plan
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function upgradePlan(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user's business
            $business = $this->businessService->getCurrentUserBusiness();
            
            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found'
                ], 404);
            }
            
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:packages,id',
                'payment_method' => 'required|string',
                'billing_cycle' => 'required|in:monthly,annual'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Process the plan upgrade using the BusinessPlanService
            $result = $this->planService->upgradePlan(
                $business,
                $request->package_id,
                $request->payment_method,
                $request->billing_cycle
            );
            
            // Return the result directly from the service
            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? null
                ], 400);
            }
            
            // If there's a redirect URL, return it
            if (isset($result['redirect_url'])) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment initiated',
                    'redirect_url' => $result['redirect_url'],
                    'payment_data' => $result['payment_data'] ?? null,
                    'method' => $result['method'] ?? 'GET'
                ]);
            }
            
            // Return the success response
            return response()->json([
                'status' => 'success',
                'message' => 'Business plan upgraded successfully',
                'data' => $result['data'] ?? [
                    'business_id' => $business->id,
                    'package_id' => $request->package_id,
                    'billing_cycle' => $request->billing_cycle,
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'payment_id' => $result['payment_id'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error upgrading business plan: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while upgrading the business plan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all unique business categories
     * 
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        $result = $this->searchService->getCategories();
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $result['data']
        ]);
    }
    
    /**
     * Get all unique business districts
     * 
     * @return JsonResponse
     */
    public function getDistricts(): JsonResponse
    {
        $result = $this->searchService->getDistricts();
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $result['data']
        ]);
    }
}
