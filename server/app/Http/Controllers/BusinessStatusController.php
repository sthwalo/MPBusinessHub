<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\BusinessStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessStatusController extends Controller
{
    protected $businessStatusService;

    /**
     * Create a new controller instance.
     *
     * @param BusinessStatusService $businessStatusService
     */
    public function __construct(BusinessStatusService $businessStatusService)
    {
        $this->businessStatusService = $businessStatusService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all possible business statuses
     *
     * @return JsonResponse
     */
    public function getStatuses(): JsonResponse
    {
        return response()->json([
            'statuses' => $this->businessStatusService->getAllStatuses()
        ]);
    }

    /**
     * Update the status of a business
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if user is admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only administrators can update business status.'
            ], 403);
        }

        // Find the business
        $business = Business::findOrFail($id);

        try {
            // Update the status
            $updatedBusiness = $this->businessStatusService->updateStatus(
                $business,
                $validated['status'],
                $validated['reason'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Business status updated successfully',
                'data' => [
                    'business' => $updatedBusiness,
                    'current_status' => $updatedBusiness->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update business status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a business
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function approveBusiness(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if user is admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only administrators can approve businesses.'
            ], 403);
        }

        // Find the business
        $business = Business::findOrFail($id);

        try {
            // Approve the business
            $updatedBusiness = $this->businessStatusService->approveBusiness(
                $business,
                $validated['reason'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Business approved successfully',
                'data' => [
                    'business' => $updatedBusiness
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve business: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a business
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function rejectBusiness(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Check if user is admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only administrators can reject businesses.'
            ], 403);
        }

        // Find the business
        $business = Business::findOrFail($id);

        try {
            // Reject the business
            $updatedBusiness = $this->businessStatusService->rejectBusiness(
                $business,
                $validated['reason']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Business rejected successfully',
                'data' => [
                    'business' => $updatedBusiness
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject business: ' . $e->getMessage()
            ], 500);
        }
    }
}