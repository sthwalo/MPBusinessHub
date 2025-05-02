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
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        
        if (Schema::hasTable('businesses') && Schema::hasColumn('businesses', 'status')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};