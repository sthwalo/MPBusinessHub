<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\OperatingHour;

class ClearBusinesses extends Command
{
    protected $signature = 'businesses:clear';
    protected $description = 'Clear all businesses and related data from the database';

    public function handle()
    {
        if ($this->confirm('Are you sure you want to delete ALL businesses? This cannot be undone.')) {
            $count = Business::count();
            
            try {
                // Start a transaction
                DB::beginTransaction();
                
                // Delete operating hours first (to respect foreign key constraints)
                $this->info('Removing operating hours...');
                OperatingHour::query()->delete();
                
                // Then delete businesses
                $this->info('Removing businesses...');
                Business::query()->delete();
                
                // Reset sequences
                $this->info('Resetting ID sequences...');
                DB::statement('ALTER SEQUENCE operating_hours_id_seq RESTART WITH 1');
                DB::statement('ALTER SEQUENCE businesses_id_seq RESTART WITH 1');
                
                // Commit the transaction
                DB::commit();
                
                $this->info("Successfully removed $count businesses and their related data.");
            } catch (\Exception $e) {
                // Rollback the transaction if something goes wrong
                DB::rollBack();
                $this->error('An error occurred: ' . $e->getMessage());
            }
        }
    }
}