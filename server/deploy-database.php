<?php

/**
 * MPBusinessHub Database Deployment Script
 * 
 * This script helps deploy the database schema and run migrations
 * in a production environment where direct command line access
 * might be limited.
 */

// Set maximum execution time to 5 minutes
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require the autoloader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "=== MPBusinessHub Database Deployment Tool ===\n\n";

// Check environment
echo "Environment: " . app()->environment() . "\n";

// Check database connection
try {
    $pdo = DB::connection()->getPdo();
    echo "Database connected successfully: " . DB::connection()->getDatabaseName() . "\n";
    echo "Database host: " . config('database.connections.pgsql.host') . "\n";
    echo "Database user: " . config('database.connections.pgsql.username') . "\n\n";
} catch (\Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Function to run Artisan commands
function runArtisan($command, $parameters = []) {
    global $kernel;
    
    echo "Running: php artisan $command " . implode(' ', $parameters) . "\n";
    
    try {
        $status = $kernel->call($command, $parameters);
        echo "Command completed with status: $status\n\n";
        return $status;
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// Clear caches
echo "=== Clearing caches ===\n";
runArtisan('config:clear');
runArtisan('cache:clear');
runArtisan('route:clear');
runArtisan('view:clear');

// Check if migrations table exists
try {
    $migrationsExist = DB::table('migrations')->exists();
    echo "Migrations table exists: " . ($migrationsExist ? "Yes" : "No") . "\n\n";
} catch (\Exception $e) {
    echo "Migrations table does not exist yet.\n\n";
    $migrationsExist = false;
}

// Run migrations
echo "=== Running migrations ===\n";
runArtisan('migrate', ['--force' => true]);

// List all tables
try {
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    echo "=== Database tables ===\n";
    foreach ($tables as $table) {
        echo "- " . $table->table_name . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "Could not list tables: " . $e->getMessage() . "\n\n";
}

// Check for specific required tables
$requiredTables = ['users', 'businesses', 'personal_access_tokens'];
echo "=== Checking required tables ===\n";
foreach ($requiredTables as $table) {
    try {
        $exists = DB::select("SELECT to_regclass('public.$table') IS NOT NULL as exists")[0]->exists;
        echo "- $table: " . ($exists ? "Exists" : "Missing") . "\n";
        
        if ($exists) {
            $count = DB::table($table)->count();
            echo "  Records: $count\n";
        }
    } catch (\Exception $e) {
        echo "- $table: Error checking - " . $e->getMessage() . "\n";
    }
}

// Check for missing columns in users table
try {
    echo "\n=== Checking users table columns ===\n";
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
    $columnNames = array_map(function($col) { return $col->column_name; }, $columns);
    
    echo "Columns found: " . implode(', ', $columnNames) . "\n\n";
    
    $requiredColumns = ['email_verified_at', 'remember_token', 'failed_login_attempts', 'locked_until'];
    foreach ($requiredColumns as $column) {
        echo "- $column: " . (in_array($column, $columnNames) ? "Exists" : "Missing") . "\n";
    }
} catch (\Exception $e) {
    echo "Error checking users table columns: " . $e->getMessage() . "\n";
}

// Create missing columns if needed
echo "\n=== Fixing missing columns ===\n";
$missingColumns = [];

// Check for email_verified_at
if (!in_array('email_verified_at', $columnNames ?? [])) {
    $missingColumns[] = 'email_verified_at';
    try {
        DB::statement('ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL');
        echo "Added email_verified_at column\n";
    } catch (\Exception $e) {
        echo "Error adding email_verified_at: " . $e->getMessage() . "\n";
    }
}

// Check for remember_token
if (!in_array('remember_token', $columnNames ?? [])) {
    $missingColumns[] = 'remember_token';
    try {
        DB::statement('ALTER TABLE users ADD COLUMN remember_token VARCHAR(100) NULL');
        echo "Added remember_token column\n";
    } catch (\Exception $e) {
        echo "Error adding remember_token: " . $e->getMessage() . "\n";
    }
}

// Check for failed_login_attempts
if (!in_array('failed_login_attempts', $columnNames ?? [])) {
    $missingColumns[] = 'failed_login_attempts';
    try {
        DB::statement('ALTER TABLE users ADD COLUMN failed_login_attempts INTEGER NOT NULL DEFAULT 0');
        echo "Added failed_login_attempts column\n";
    } catch (\Exception $e) {
        echo "Error adding failed_login_attempts: " . $e->getMessage() . "\n";
    }
}

// Check for locked_until
if (!in_array('locked_until', $columnNames ?? [])) {
    $missingColumns[] = 'locked_until';
    try {
        DB::statement('ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL');
        echo "Added locked_until column\n";
    } catch (\Exception $e) {
        echo "Error adding locked_until: " . $e->getMessage() . "\n";
    }
}

if (empty($missingColumns)) {
    echo "No missing columns to fix\n";
}

// Verify existing accounts
echo "\n=== Verifying existing accounts ===\n";
try {
    $unverifiedUsers = DB::table('users')
        ->whereNull('email_verified_at')
        ->count();
    
    echo "Found $unverifiedUsers unverified users\n";
    
    if ($unverifiedUsers > 0) {
        $updated = DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
        
        echo "Verified $updated existing accounts\n";
    }
} catch (\Exception $e) {
    echo "Error verifying accounts: " . $e->getMessage() . "\n";
}

echo "\n=== Deployment Complete ===\n";
echo "</pre>";
