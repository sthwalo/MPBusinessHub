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
            case 'week':
            default:
                $startDate = $now->copy()->subWeek();
                $previousStart = $now->copy()->subWeeks(2);
                break;
        }
        
        $previousEnd = $startDate;
        
        // User statistics
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $previousUsers = User::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $userGrowth = $previousUsers > 0 ? round((($newUsers - $previousUsers) / $previousUsers) * 100, 2) : 0;
        
        // Business statistics
        $totalBusinesses = Business::count();
        $newBusinesses = Business::where('created_at', '>=', $startDate)->count();
        $previousBusinesses = Business::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $businessGrowth = $previousBusinesses > 0 ? round((($newBusinesses - $previousBusinesses) / $previousBusinesses) * 100, 2) : 0;
        
        // Revenue statistics
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $newRevenue = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');
        $previousRevenue = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('amount');
        $revenueGrowth = $previousRevenue > 0 ? round((($newRevenue - $previousRevenue) / $previousRevenue) * 100, 2) : 0;
        
        // Business status counts
        $pendingBusinesses = Business::where('status', 'pending')->count();
        $approvedBusinesses = Business::where('status', 'approved')->count();
        $rejectedBusinesses = Business::where('status', 'rejected')->count();
        
        // Recent activity
        $recentActivity = [];
        
        // Recent user registrations
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        foreach ($recentUsers as $user) {
            $recentActivity[] = [
                'description' => 'New user registration',
                'user' => $user->name,
                'time' => $user->created_at->diffForHumans()
            ];
        }
        
        // Recent business registrations
        $recentBusinesses = Business::orderBy('created_at', 'desc')->take(5)->get();
        foreach ($recentBusinesses as $business) {
            $recentActivity[] = [
                'description' => 'New business registration',
                'user' => $business->name,
                'time' => $business->created_at->diffForHumans()
            ];
        }
        
        // Recent reviews
        $recentReviews = Review::orderBy('created_at', 'desc')->take(5)->get();
        foreach ($recentReviews as $review) {
            $recentActivity[] = [
                'description' => 'New review submitted',
                'user' => $review->reviewer_name,
                'time' => $review->created_at->diffForHumans()
            ];
        }
        
        // Sort recent activity by time
        usort($recentActivity, function($a, $b) {
            return Carbon::parse($b['time']) <=> Carbon::parse($a['time']);
        });
        
        // Limit to 10 most recent activities
        $recentActivity = array_slice($recentActivity, 0, 10);
        
        return response()->json([
            'users' => [
                'total' => $totalUsers,
                'new' => $newUsers,
                'growth' => $userGrowth
            ],
            'businesses' => [
                'total' => $totalBusinesses,
                'new' => $newBusinesses,
                'growth' => $businessGrowth
            ],
            'reviews' => [
                'total' => $totalReviews,
                'new' => $newReviews,
                'growth' => $reviewGrowth
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'new' => $newRevenue,
                'growth' => $revenueGrowth
            ],
            'businessStatus' => [
                'pending' => $pendingBusinesses,
                'approved' => $approvedBusinesses,
                'rejected' => $rejectedBusinesses
            ],
            'recentActivity' => $recentActivity
        ]);
    }
}