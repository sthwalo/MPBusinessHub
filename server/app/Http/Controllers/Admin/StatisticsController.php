<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Business;
use App\Models\Review;
use App\Models\Payment;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function getStatistics(Request $request)
    {
        $range = $request->query('range', 'week');
        $now = Carbon::now();

        switch ($range) {
            case 'day':
                $startDate = $now->copy()->subDay();
                $previousStart = $now->copy()->subDays(2);
                break;
            case 'month':
                $startDate = $now->copy()->subMonth();
                $previousStart = $now->copy()->subMonths(2);
                break;
            case 'year':
                $startDate = $now->copy()->subYear();
                $previousStart = $now->copy()->subYears(2);
                break;
            default: // week
                $startDate = $now->copy()->subWeek();
                $previousStart = $now->copy()->subWeeks(2);
                break;
        }

        $previousEnd = $startDate;

        // User statistics
        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->count();
        $moderatorUsers = User::where('role', 'moderator')->count();
        $regularUsers = User::where('role', 'user')->count();

        // Business statistics
        $totalBusinesses = Business::count();
        $pendingBusinesses = Business::where('status', 'pending')->count();
        $approvedBusinesses = Business::where('status', 'approved')->count();
        $rejectedBusinesses = Business::where('status', 'rejected')->count();

        // Review statistics
        $totalReviews = Review::count();
        $pendingReviews = Review::where('status', 'pending')->count();
        $approvedReviews = Review::where('status', 'approved')->count();
        $rejectedReviews = Review::where('status', 'rejected')->count();

        return response()->json([
            'users' => [
                'total' => $totalUsers ?? 0,
                'admin' => $adminUsers ?? 0,
                'moderator' => $moderatorUsers ?? 0,
                'user' => $regularUsers ?? 0,
            ],
            'businesses' => [
                'total' => $totalBusinesses ?? 0,
                'pending' => $pendingBusinesses ?? 0,
                'approved' => $approvedBusinesses ?? 0,
                'rejected' => $rejectedBusinesses ?? 0,
            ],
            'reviews' => [
                'total' => $totalReviews ?? 0,
                'pending' => $pendingReviews ?? 0,
                'approved' => $approvedReviews ?? 0,
                'rejected' => $rejectedReviews ?? 0,
            ],
        ]);
    }
}