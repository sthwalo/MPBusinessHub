<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessRegistrationRequest;
use App\Services\BusinessRegistrationService;
use Illuminate\Http\JsonResponse;
use Exception;

class BusinessRegistrationController extends Controller
{
    /**
     * @var BusinessRegistrationService
     */
    protected $businessRegistrationService;

    /**
     * BusinessRegistrationController constructor.
     * 
     * @param BusinessRegistrationService $businessRegistrationService
     */
    public function __construct(BusinessRegistrationService $businessRegistrationService)
    {
        $this->businessRegistrationService = $businessRegistrationService;
    }

    /**
     * Register a new business with user account
     * 
     * @param BusinessRegistrationRequest $request
     * @return JsonResponse
     */
    public function register(BusinessRegistrationRequest $request): JsonResponse
    {
        try {
            $result = $this->businessRegistrationService->register($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Business registered successfully. Please check your email to verify your account.',
                'data' => $result
            ], 201);
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $statusCode === 422 ? ['validation' => $e->getMessage()] : null
            ], $statusCode);
        }
    }
}
