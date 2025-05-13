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
        // Skip this migration in testing environment
        if (app()->environment('testing')) {
            return;
        }
        
        // Update the invoices table if it exists
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Add currency field if it doesn't exist
                if (!Schema::hasColumn('invoices', 'currency')) {
                    $table->string('currency', 3)->default('ZAR')->after('amount');
                }
                
                // Add payment_method field if it doesn't exist
                if (!Schema::hasColumn('invoices', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('currency');
                }
                
                // Add change_type field if it doesn't exist (upgrade, downgrade, renewal)
                if (!Schema::hasColumn('invoices', 'change_type')) {
                    $table->string('change_type')->default('upgrade')->after('description');
                }
            });
        }
        
        // Update the payments table
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                // Add currency field if it doesn't exist
                if (!Schema::hasColumn('payments', 'currency')) {
                    $table->string('currency', 3)->default('ZAR')->after('amount');
                }
                
                // Add payment_method field if it doesn't exist
                if (!Schema::hasColumn('payments', 'payment_method')) {
                    $table->string('payment_method')->default('credit_card')->after('currency');
                }
                
                // Add payment_gateway field if it doesn't exist
                if (!Schema::hasColumn('payments', 'payment_gateway')) {
                    $table->string('payment_gateway')->nullable()->after('payment_method');
                }
                
                // Add notes field if it doesn't exist
                if (!Schema::hasColumn('payments', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip this migration in testing environment
        if (app()->environment('testing')) {
            return;
        }
        
        // Remove the added columns from invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'currency')) {
                    $table->dropColumn('currency');
                }
                
                if (Schema::hasColumn('invoices', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
                
                if (Schema::hasColumn('invoices', 'change_type')) {
                    $table->dropColumn('change_type');
                }
            });
        }
        
        // Remove the added columns from payments table
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'currency')) {
                    $table->dropColumn('currency');
                }
                
                if (Schema::hasColumn('payments', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
                
                if (Schema::hasColumn('payments', 'payment_gateway')) {
                    $table->dropColumn('payment_gateway');
                }
                
                if (Schema::hasColumn('payments', 'notes')) {
                    $table->dropColumn('notes');
                }
            });
        }
    }
};
