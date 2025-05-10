<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Record the start time
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        // Calculate the execution time
        $executionTime = microtime(true) - $startTime;
        
        // Log the performance data
        Log::channel('performance')->info('API Performance', [
            'uri' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            'status_code' => $response->getStatusCode(),
        ]);
        
        // Add performance headers to the response
        if (app()->environment('local', 'development')) {
            $response->headers->set('X-Execution-Time', round($executionTime * 1000, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB');
        }
        
        return $response;
    }
}