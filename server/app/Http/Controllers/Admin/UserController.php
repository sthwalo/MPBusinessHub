<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }
    
    public function updateRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:user,admin'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        
        $user->role = $request->role;
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'User role updated successfully',
            'data' => $user
        ]);
    }
}