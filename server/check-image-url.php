<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the business
$business = \App\Models\Business::first();

if (!$business) {
    echo "No business found in the database.\n";
    exit;
}

echo "Business: {$business->name} (ID: {$business->id})\n";
echo "Image URL from database: " . ($business->image_url ?: 'NULL') . "\n";

// Extract the path relative to storage/app/public
$relativePath = str_replace('/storage/', '', $business->image_url);
$fullPath = storage_path('app/public/' . $relativePath);

echo "Checking if file exists at: {$fullPath}\n";

if (file_exists($fullPath)) {
    echo "✅ File EXISTS\n";
    echo "File size: " . filesize($fullPath) . " bytes\n";
} else {
    echo "❌ File DOES NOT EXIST\n";
    
    // Check if the directory exists
    $dir = dirname($fullPath);
    echo "Directory {$dir} " . (is_dir($dir) ? "exists" : "does not exist") . "\n";
}