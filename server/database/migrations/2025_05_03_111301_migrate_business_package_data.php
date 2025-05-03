<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // Get all packages
    $packages = DB::table('packages')->get();
    
    // Create a mapping of package names to IDs
    $packageMap = [];
    foreach ($packages as $package) {
        $packageMap[$package->name] = $package->id;
    }
    
    // Update all businesses
    $businesses = DB::table('businesses')->get();
    foreach ($businesses as $business) {
        $packageId = null;
        
        // Map the old package_type to the new package_id
        if (isset($business->package_type) && isset($packageMap[$business->package_type])) {
            $packageId = $packageMap[$business->package_type];
        } else {
            // Default to Basic package if no match
            $packageId = $packageMap['Basic'] ?? null;
        }
        
        if ($packageId) {
            DB::table('businesses')
                ->where('id', $business->id)
                ->update(['package_id' => $packageId]);
        }
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
