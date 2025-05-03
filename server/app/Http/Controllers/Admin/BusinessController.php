<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

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
}