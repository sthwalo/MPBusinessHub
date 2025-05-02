<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Get the current user's active sessions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();
            
            $currentTokenId = $request->user()->currentAccessToken()->id;
            
            $sessions = $tokens->map(function ($token) use ($currentTokenId) {
                $expiresAt = $token->created_at->addMinutes(config('sanctum.expiration', 60 * 24));
                
                return [
                    'id' => $token->id,
                    'device' => $token->name,
                    'ip_address' => $token->last_used_ip ?? 'Unknown',
                    'last_active' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
                    'created_at' => $token->created_at->format('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'is_current_device' => $token->id === $currentTokenId,
                ];
            });
            
            return response()->json([
                'sessions' => $sessions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve sessions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke a specific session token
     *
     * @param Request $request
     * @param int $tokenId
     * @return JsonResponse
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->findOrFail($tokenId);
        
        // Don't allow revoking the current token
        if ($token->id === $request->user()->currentAccessToken()->id) {
            return response()->json([
                'message' => 'Cannot revoke the current session',
            ], 400);
        }
        
        $token->delete();
        
        return response()->json([
            'message' => 'Session revoked successfully',
        ]);
    }

    /**
     * Revoke all sessions except the current one
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()->id;
        
        $request->user()->tokens()->where('id', '!=', $currentTokenId)->delete();
        
        return response()->json([
            'message' => 'All other sessions revoked successfully',
        ]);
    }

    /**
     * Update the last activity timestamp for the current token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateActivity(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $token->last_used_at = now();
        $token->last_used_ip = $request->ip();
        $token->save();
        
        return response()->json([
            'message' => 'Activity updated',
        ]);
    }
}