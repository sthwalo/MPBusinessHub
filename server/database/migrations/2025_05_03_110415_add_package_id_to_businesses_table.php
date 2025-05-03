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
        Schema::table('businesses', function (Blueprint $table) {
            // Add package_id as a foreign key
            $table->foreignId('package_id')->nullable()->after('user_id')->constrained();
            
            // Add billing_cycle column
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly')->after('package_id');
            
            // Add subscription_ends_at column
            $table->timestamp('subscription_ends_at')->nullable()->after('billing_cycle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn(['package_id', 'billing_cycle', 'subscription_ends_at']);
        });
    }
};