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
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
});

// Public review routes
Route::post('/reviews/anonymous', [App\Http\Controllers\ReviewController::class, 'storeAnonymousReview']);
Route::get('/reviews', [App\Http\Controllers\ReviewController::class, 'index']);
Route::get('/businesses/{businessId}/reviews', [App\Http\Controllers\ReviewController::class, 'getBusinessReviews']);

// Advert routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/adverts', [App\Http\Controllers\AdvertController::class, 'store']);
    Route::get('/adverts', [App\Http\Controllers\AdvertController::class, 'index']);
    Route::delete('/adverts/{id}', [App\Http\Controllers\AdvertController::class, 'destroy']);
});

// Product routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index']);
    Route::post('/products', [App\Http\Controllers\ProductController::class, 'store']);
    Route::get('/products/{id}', [App\Http\Controllers\ProductController::class, 'show']);
    Route::put('/products/{id}', [App\Http\Controllers\ProductController::class, 'update']);
    Route::delete('/products/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);
});

// Public product routes
Route::get('/businesses/{id}/products', function ($id) {
    $products = App\Models\Product::where('business_id', $id)
        ->where('status', 'active')
        ->get();
        
    return response()->json([
        'status' => 'success',
        'data' => $products
    ]);
});

// Package routes
Route::get('/packages', [App\Http\Controllers\PackageController::class, 'index']);
Route::middleware('auth:sanctum')->post('/packages/upgrade', [App\Http\Controllers\PackageController::class, 'upgrade']);

// Business statistics tracking
Route::post('/businesses/{id}/view', [BusinessController::class, 'incrementViewCount']);
Route::post('/businesses/{id}/contact', [BusinessController::class, 'incrementContactCount']);

// Image upload routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/images/business', [App\Http\Controllers\ImageController::class, 'uploadBusinessImage']);
    Route::post('/images/product', [App\Http\Controllers\ImageController::class, 'uploadProductImage']);
});
// Business Status Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/business-statuses', [App\Http\Controllers\BusinessStatusController::class, 'getStatuses']);
    Route::put('/businesses/{id}/status', [App\Http\Controllers\BusinessStatusController::class, 'updateStatus']);
    Route::put('/businesses/{id}/approve', [App\Http\Controllers\BusinessStatusController::class, 'approveBusiness']);
    Route::put('/businesses/{id}/reject', [App\Http\Controllers\BusinessStatusController::class, 'rejectBusiness']);
});
// User Role Routes
Route::middleware('auth:sanctum')->get('/user/role', [App\Http\Controllers\AuthController::class, 'getUserRole']);

// Add these routes to /server/routes/api.php

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Business management
    Route::get('/businesses', [App\Http\Controllers\Admin\BusinessController::class, 'index']);
    
    // User management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::put('/users/{id}/role', [App\Http\Controllers\Admin\UserController::class, 'updateRole']);
    
    // Content moderation
    Route::get('/reviews/pending', [App\Http\Controllers\Admin\ReviewController::class, 'getPendingReviews']);
    Route::put('/reviews/{id}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approveReview']);
    Route::put('/reviews/{id}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'rejectReview']);
    
    // System statistics
    Route::get('/statistics', [App\Http\Controllers\Admin\StatisticsController::class, 'getStatistics']);
});