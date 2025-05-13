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
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
        
        // Add status column to businesses table if it doesn't exist
        if (Schema::hasTable('businesses') && !Schema::hasColumn('businesses', 'status')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->string('status')->default('pending'); // Use string instead of enum for flexibility
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('businesses')) {
            // Drop foreign key constraint on businesses table
            Schema::table('businesses', function (Blueprint $table) {
                if (Schema::hasColumn('businesses', 'user_id')) {
                    $table->dropForeign(['user_id']); // Replace 'user_id' with the actual foreign key column name
                }
                if (Schema::hasColumn('businesses', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }

        // Drop users table
        Schema::dropIfExists('users');
    }
};