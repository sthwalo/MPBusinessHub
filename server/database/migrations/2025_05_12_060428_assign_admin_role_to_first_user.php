<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Assign admin role to the first user and verify their email.
     */
    public function up(): void
    {
        // Set the first user as admin and verify email
        DB::table('users')
            ->orderBy('id')
            ->limit(1)
            ->update([
                'role' => 'admin',
                'email_verified_at' => Carbon::now() // Auto-verify the admin's email
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->orderBy('id')
            ->limit(1)
            ->update([
                'role' => 'user',
                'email_verified_at' => null
            ]);
    }
};
