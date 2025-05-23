<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\BusinessRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class BusinessRegistrationService
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var BusinessRepository
     */
    protected $businessRepository;

    /**
     * BusinessRegistrationService constructor.
     * 
     * @param UserRepository $userRepository
     * @param BusinessRepository $businessRepository
     */
    public function __construct(
        UserRepository $userRepository,
        BusinessRepository $businessRepository
    ) {
        $this->userRepository = $userRepository;
        $this->businessRepository = $businessRepository;
    }

    /**
     * Register a new business with user account
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function register(array $data)
    {
        // Check if email already exists
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new Exception('Email already registered', 409);
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = $this->userRepository->createUser([
                'name' => $data['businessName'] ?? 'User',
                'email' => $data['email'],
                'password' => $data['password'], 
            ]);

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            // Create business
            $business = $this->businessRepository->createBusiness([
                'name' => $data['businessName'],
                'description' => $data['description'],
                'category' => $data['category'],
                'district' => $data['district'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'website' => $data['website'] ?? null,
            ], $user->id);

            // Generate token for user
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified' => false
                ],
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'status' => $business->status
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business registration failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
