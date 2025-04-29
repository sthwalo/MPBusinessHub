<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ClearOrphanedUsers extends Command
{
    protected $signature = 'users:clear-orphaned';
    protected $description = 'Clear all users that do not have associated businesses';

    public function handle()
    {
        if ($this->confirm('Are you sure you want to delete all users without businesses? This cannot be undone.')) {
            // Find users without businesses
            $orphanedUsers = User::whereNotIn('id', function($query) {
                $query->select('user_id')->from('businesses');
            })->get();
            
            $count = $orphanedUsers->count();
            
            if ($count > 0) {
                // Delete the orphaned users
                User::whereNotIn('id', function($query) {
                    $query->select('user_id')->from('businesses');
                })->delete();
                
                $this->info("Successfully removed $count users without associated businesses.");
            } else {
                $this->info("No orphaned users found.");
            }
        }
    }
}