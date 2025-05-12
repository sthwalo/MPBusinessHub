<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Business;
use App\Services\PackageService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    protected $packageService;
    protected $paymentService;

    /**
     * Constructor
     */
    public function __construct(PackageService $packageService, PaymentService $paymentService)
    {
        $this->packageService = $packageService;
        $this->paymentService = $paymentService;
    }

    /**
     * Get all packages
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $packages = $this->packageService->getAllPackages();
        
        if ($packages->isEmpty()) {
            return response()->json([
                'status' => 'warning',
                'message' => 'No packages found in the database',
                'data' => []
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $packages
        ]);
    }

    /**
     * Get a specific package
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $package = $this->packageService->getPackageById($id);
        
        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Package not found'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $package
        ]);
    }

    /**
     * Process a package upgrade/downgrade
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upgradeBusinessPlan(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'package_id' => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get the business
        $business = Business::find($request->business_id);
        
        // Check if the user is authorized to update this business
        if (Auth::id() !== $business->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this business'
            ], 403);
        }
        
        // Process the package change
        $result = $this->paymentService->processPackageChange(
            $business,
            $request->package_id,
            $request->billing_cycle
        );
        
        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 400);
        }
        
        // Return the updated business data
        return response()->json([
            'status' => 'success',
            'message' => 'Business plan updated successfully',
            'data' => [
                'business' => $business->fresh(),
                'payment' => [
                    'invoice_id' => $result['invoice_id'],
                    'amount' => $result['amount']
                ]
            ]
        ]);
    }

    /**
     * Compare two packages
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comparePackages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_package' => 'required',
            'new_package' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->packageService->comparePackages(
            $request->current_package,
            $request->new_package
        );
        
        if ($result === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'One or both packages not found'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'comparison' => $result
            ]
        ]);
    }
}