<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Maximum number of failed login attempts before locking
     */
    protected $maxAttempts = 5;

    /**
     * Minutes to lock the account after too many failed attempts
     */
    protected $lockoutTime = 10;

    /**
     * Handle user login
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Find the user by email
        $user = User::where('email', $request->email)->first();
    
        // If user doesn't exist, return generic error message
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        // Check if account is locked
        if ($this->isAccountLocked($user)) {
            $lockExpiration = Carbon::parse($user->locked_until);
            $minutesLeft = now()->diffInMinutes($lockExpiration);
            
            return response()->json([
                'success' => false,
                'message' => "Your account is locked due to too many failed login attempts. Please try again in {$minutesLeft} minutes or reset your password.",
                'locked_until' => $user->locked_until,
            ], 403);
        }
    
        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            // Increment failed login attempts
            $this->incrementFailedAttempts($user);
            
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        // Reset failed login attempts on successful login
        $this->resetFailedAttempts($user);
    
        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email not verified. Please check your email for verification link.',
                'email_verified' => false,
                'user_id' => $user->id,
                'email' => $user->email
            ], 403);
        }
    
        // Find associated business
        $business = Business::where('user_id', $user->id)->first();
    
        // Create token
        $token = $user->createToken($request->device_name ?? $request->ip())->plainTextToken;
    
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified' => true
                ],
                'business' => $business ? [
                    'id' => $business->id,
                    'name' => $business->name,
                    'status' => $business->status,
                ] : null,
            ]
        ]);
    }

    /**
     * Handle user logout
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Check if the user account is locked
     *
     * @param User $user
     * @return bool
     */
    protected function isAccountLocked(User $user): bool
    {
        return $user->locked_until && now()->lt(Carbon::parse($user->locked_until));
    }

    /**
     * Increment failed login attempts and lock account if needed
     *
     * @param User $user
     * @return void
     */
    protected function incrementFailedAttempts(User $user): void
    {
        $user->failed_login_attempts += 1;
        
        // Lock the account if max attempts reached
        if ($user->failed_login_attempts >= $this->maxAttempts) {
            $user->locked_until = now()->addMinutes($this->lockoutTime);
        }
        
        $user->save();
    }

    /**
     * Reset failed login attempts
     *
     * @param User $user
     * @return void
     */
    protected function resetFailedAttempts(User $user): void
    {
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();
    }
    
    public function getUserRole()
    {
        $user = Auth::user();
        return response()->json([
            'role' => $user->role
        ]);
    }
}
