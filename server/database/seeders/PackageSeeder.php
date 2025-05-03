<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::create([
            'name' => 'Basic',
            'description' => 'Basic listing for your business',
            'price_monthly' => 0,
            'price_annual' => 0,
            'advert_limit' => 0,
            'product_limit' => 0,
            'features' => json_encode(['Basic listing', 'Business hours']),
            'is_active' => true
        ]);

        Package::create([
            'name' => 'Bronze',
            'description' => 'Enhanced listing with contact information',
            'price_monthly' => 200,
            'price_annual' => 2000,
            'advert_limit' => 0,
            'product_limit' => 0,
            'features' => json_encode(['Basic listing', 'Business hours', 'Contact information']),
            'is_active' => true
        ]);

        Package::create([
            'name' => 'Silver',
            'description' => 'Premium listing with products and adverts',
            'price_monthly' => 500,
            'price_annual' => 5000,
            'advert_limit' => 2,
            'product_limit' => 10,
            'features' => json_encode(['Basic listing', 'Business hours', 'Contact information', 'Products showcase', '2 monthly adverts']),
            'is_active' => true
        ]);

        Package::create([
            'name' => 'Gold',
            'description' => 'Ultimate business promotion package',
            'price_monthly' => 1000,
            'price_annual' => 10000,
            'advert_limit' => 4,
            'product_limit' => 50,
            'features' => json_encode(['Basic listing', 'Business hours', 'Contact information', 'Products showcase', '4 monthly adverts', 'Featured listing']),
            'is_active' => true
        ]);
    }
}