<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTokens extends Command
{
    protected $signature = 'tokens:clear';
    protected $description = 'Clear all personal access tokens';

    public function handle()
    {
        if ($this->confirm('Are you sure you want to delete ALL user tokens? This will log everyone out.')) {
            $count = DB::table('personal_access_tokens')->count();
            
            DB::table('personal_access_tokens')->truncate();
            
            $this->info("Successfully removed $count tokens. All users have been logged out.");
        }
    }
}