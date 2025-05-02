<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\SessionController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/businesses', [BusinessController::class, 'index']);
Route::get('/businesses/check', [BusinessController::class, 'check']);
Route::post('/businesses/register', [BusinessRegistrationController::class, 'register']);
// Dynamic route with {id} parameter must come after specific routes
Route::get('/businesses/{id}', [BusinessController::class, 'show']);

// Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Password Reset Routes
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');

// Test route for verification email
Route::get('/test-verification-email/{email}', function($email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent to ' . $email]);
    }
    return response()->json(['message' => 'User not found'], 404);
});

// Protected business routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/business/details', [BusinessController::class, 'getBusinessDetails']);
    Route::put('/business/update', [BusinessController::class, 'updateBusinessProfile']);
    Route::delete('/business/remove-all', [BusinessController::class, 'removeAllBusinesses']); // Admin route to remove all businesses
});

// This route has been replaced by the BusinessController check method above

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Session management routes
// Session management routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sessions', [SessionController::class, 'index']);
    Route::delete('/sessions/{token}', [SessionController::class, 'destroy']);
    Route::delete('/sessions', [SessionController::class, 'destroyAll']);
    Route::post('/sessions/activity', [SessionController::class, 'updateActivity']);
});

// Review routes
// Review routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
});