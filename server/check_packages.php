<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check if packages exist in the database
$packages = \App\Models\Package::all();

if ($packages->isEmpty()) {
    echo "No packages found in the database\n";
} else {
    echo "Found " . $packages->count() . " packages in the database:\n";
    foreach ($packages as $package) {
        echo "ID: {$package->id}, Name: {$package->name}, Monthly Price: {$package->price_monthly}, Annual Price: {$package->price_annual}, Advert Limit: {$package->advert_limit}\n";
    }
}

// Check if the PackageController exists
$controllerPath = app_path('Http/Controllers/PackageController.php');
if (file_exists($controllerPath)) {
    echo "\nPackageController exists at: {$controllerPath}\n";
    
    // Check the route for packages
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $packageRouteFound = false;
    
    foreach ($routes as $route) {
        if ($route->uri() === 'api/packages') {
            $packageRouteFound = true;
            echo "Route for packages found: {$route->uri()} [{$route->methods()[0]}]\n";
            break;
        }
    }
    
    if (!$packageRouteFound) {
        echo "No route found for api/packages\n";
    }
} else {
    echo "\nPackageController does not exist at: {$controllerPath}\n";
}
