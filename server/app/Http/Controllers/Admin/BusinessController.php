<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class BusinessController extends Controller
{
    /**
     * Display a listing of the businesses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $businesses = Business::with('user')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $businesses
        ]);
    }
    
    /**
     * Approve a business.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveBusiness($id)
    {
        $business = Business::with('user')->find($id);
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        $business->status = 'approved';
        $business->save();
        
        // Send notification email to business owner
        try {
            $user = $business->user;
            
            // Send email notification
            Mail::raw(
                "Dear {$user->name},\n\nCongratulations! Your business '{$business->name}' has been approved and is now listed in our directory.\n\nThank you for using MPBusinessHub.\n\nBest regards,\nThe MPBusinessHub Team",
                function ($message) use ($user, $business) {
                    $message->to($user->email)
                            ->subject("Your Business '{$business->name}' Has Been Approved");
                }
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the approval process
            \Illuminate\Support\Facades\Log::error('Failed to send approval email: ' . $e->getMessage());
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Business approved successfully',
            'data' => $business
        ]);
    }
    
    /**
     * Reject a business.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectBusiness(Request $request, $id)
    {
        $business = Business::with('user')->find($id);
        
        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found'
            ], 404);
        }
        
        $business->status = 'rejected';
        $business->rejection_reason = $request->reason;
        $business->save();
        
        // Send notification email to business owner
        try {
            $user = $business->user;
            $reason = $request->reason ?? 'No specific reason provided.';
            
            // Send email notification
            Mail::raw(
                "Dear {$user->name},\n\nWe regret to inform you that your business '{$business->name}' has been rejected for the following reason:\n\n{$reason}\n\nIf you believe this is an error or would like to provide additional information, please contact our support team.\n\nThank you for using MPBusinessHub.\n\nBest regards,\nThe MPBusinessHub Team",
                function ($message) use ($user, $business) {
                    $message->to($user->email)
                            ->subject("Your Business '{$business->name}' Has Been Rejected");
                }
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the rejection process
            \Illuminate\Support\Facades\Log::error('Failed to send rejection email: ' . $e->getMessage());
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Business rejected successfully',
            'data' => $business
        ]);
    }
}