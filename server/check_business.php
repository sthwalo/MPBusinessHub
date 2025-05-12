<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the user with email sthwaloe@gmail.com
$user = \App\Models\User::where('email', 'sthwaloe@gmail.com')->first();

if (!$user) {
    echo "User with email sthwaloe@gmail.com not found\n";
    exit;
}

echo "Found user: ID {$user->id}, Name: {$user->name}, Email: {$user->email}\n";

// Check if there's a business linked to this user
$business = \App\Models\Business::where('user_id', $user->id)->first();

if ($business) {
    echo "Business found for user:\n";
    echo "ID: {$business->id}\n";
    echo "Name: {$business->name}\n";
    echo "Status: {$business->status}\n";
    echo "Package ID: {$business->package_id}\n";
    echo "Package Type: {$business->package_type}\n";
} else {
    echo "No business found for this user\n";
    
    // Check if there are any businesses in the database
    $allBusinesses = \App\Models\Business::all();
    
    if ($allBusinesses->isEmpty()) {
        echo "No businesses found in the database\n";
    } else {
        echo "Found {$allBusinesses->count()} businesses in the database:\n";
        
        foreach ($allBusinesses as $b) {
            echo "ID: {$b->id}, Name: {$b->name}, User ID: {$b->user_id}\n";
        }
    }
}
