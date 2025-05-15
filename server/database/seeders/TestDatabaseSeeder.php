<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;

class TestDatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'is_admin' => true,
        ]);
        
        // Create standard package types
        $packageTypes = ['Free', 'Bronze', 'Silver', 'Gold'];
        
        // Create businesses with different package types
        foreach ($packageTypes as $packageType) {
            $user = User::factory()->create();
            Business::factory()->create([
                'user_id' => $user->id,
                'package_type' => $packageType,
                'status' => 'approved'
            ]);
        }
    }
}