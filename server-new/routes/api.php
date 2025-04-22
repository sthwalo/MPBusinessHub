<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Controllers\BusinessController;

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
Route::post('/businesses/register', [BusinessRegistrationController::class, 'register']);
Route::get('/businesses', [BusinessController::class, 'index']);
Route::get('/businesses/check', [BusinessController::class, 'check']);
Route::get('/businesses/{id}', [BusinessController::class, 'show']);

// Authentication routes
Route::post('/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected business routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/business/details', [BusinessController::class, 'getBusinessDetails']);
    Route::put('/business/update', [BusinessController::class, 'updateBusinessProfile']);
});

// Temporary route to check business data
Route::get('/businesses/check', function() {
    $businesses = App\Models\Business::with('user')->get();
    return response()->json(['businesses' => $businesses]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
