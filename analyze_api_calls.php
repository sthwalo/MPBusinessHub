<?php

/**
 * Analyzes API calls in the project by scanning relevant files
 *
 * @param string $rootDir Root directory of the project
 * @return array Analysis results
 */
function countApiCalls($rootDir) {
    $apiCalls = [
        'frontend' => [
            'axios_calls' => 0,
            'fetch_calls' => 0,
            'endpoints' => []
        ],
        'backend' => [
            'routes' => 0,
            'controller_methods' => 0,
            'endpoints' => []
        ]
    ];
    
    // Frontend analysis (React files)
    $frontendDir = $rootDir . '/client/src';
    $frontendFiles = getFilesRecursively($frontendDir, ['js', 'jsx']);
    
    foreach ($frontendFiles as $file) {
        $content = file_get_contents($file);
        
        // Count axios calls
        preg_match_all('/axios\.(get|post|put|delete|patch)/', $content, $axiosMatches);
        $apiCalls['frontend']['axios_calls'] += count($axiosMatches[0]);
        
        // Count fetch calls
        preg_match_all('/fetch\(/', $content, $fetchMatches);
        $apiCalls['frontend']['fetch_calls'] += count($fetchMatches[0]);
        
        // Extract endpoints
        preg_match_all('/\/api\/[a-zA-Z0-9\-\_\/]+/', $content, $endpointMatches);
        $apiCalls['frontend']['endpoints'] = array_unique(
            array_merge($apiCalls['frontend']['endpoints'], $endpointMatches[0])
        );
    }
    
    // Backend analysis (Laravel routes and controllers)
    $backendDir = $rootDir . '/server';
    
    // Analyze routes/api.php
    $routesFile = $backendDir . '/routes/api.php';
    if (file_exists($routesFile)) {
        $content = file_get_contents($routesFile);
        
        // Count route definitions
        preg_match_all('/Route::(get|post|put|delete|patch)/', $content, $routeMatches);
        $apiCalls['backend']['routes'] = count($routeMatches[0]);
        
        // Extract endpoints
        preg_match_all('/\'\/[a-zA-Z0-9\-\_\/]+\'/', $content, $endpointMatches);
        $apiCalls['backend']['endpoints'] = array_unique(
            array_merge($apiCalls['backend']['endpoints'], $endpointMatches[0])
        );
    }
    
    // Analyze controllers
    $controllersDir = $backendDir . '/app/Http/Controllers';
    if (is_dir($controllersDir)) {
        $controllerFiles = getFilesRecursively($controllersDir, ['php']);
        
        foreach ($controllerFiles as $file) {
            if (strpos(basename($file), 'Controller.php') !== false) {
                $content = file_get_contents($file);
                
                // Count public methods (potential API endpoints)
                preg_match_all('/public function/', $content, $methodMatches);
                $apiCalls['backend']['controller_methods'] += count($methodMatches[0]);
            }
        }
    }
    
    return $apiCalls;
}

/**
 * Get all files recursively from a directory with specific extensions
 *
 * @param string $dir Directory to scan
 * @param array $extensions File extensions to include
 * @return array List of file paths
 */
function getFilesRecursively($dir, $extensions = []) {
    $files = [];
    
    if (!is_dir($dir)) {
        return $files;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if (empty($extensions) || in_array($extension, $extensions)) {
                $files[] = $file->getPathname();
            }
        }
    }
    
    return $files;
}

/**
 * Generates a formatted report of the API call analysis
 *
 * @param array $apiCalls Analysis results
 * @return string Formatted report
 */
function generateReport($apiCalls) {
    $frontendEndpoints = implode("\n", array_map(function($endpoint) {
        return "  - " . $endpoint;
    }, $apiCalls['frontend']['endpoints']));
    
    $backendEndpoints = implode("\n", array_map(function($endpoint) {
        return "  - " . $endpoint;
    }, $apiCalls['backend']['endpoints']));
    
    $totalFrontendCalls = $apiCalls['frontend']['axios_calls'] + $apiCalls['frontend']['fetch_calls'];
    
    $report = <<<EOT
📊 API Calls Analysis Report
==========================

Frontend Analysis:
----------------
- Axios API Calls: {$apiCalls['frontend']['axios_calls']}
- Fetch API Calls: {$apiCalls['frontend']['fetch_calls']}
- Total Frontend API Calls: {$totalFrontendCalls}

Frontend Endpoints Used:
{$frontendEndpoints}

Backend Analysis:
---------------
- Route Definitions: {$apiCalls['backend']['routes']}
- Controller Methods: {$apiCalls['backend']['controller_methods']}

Backend Endpoints Defined:
{$backendEndpoints}
EOT;
    
    return $report;
}

// Main execution
$rootDir = __DIR__;  // Current directory
$results = countApiCalls($rootDir);
$report = generateReport($results);

// Print report to console
echo $report;

// Save report to file
file_put_contents('api_analysis_report.txt', $report);