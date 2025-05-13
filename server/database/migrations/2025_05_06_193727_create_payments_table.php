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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('business_id')->nullable()->constrained();
            $table->string('package_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ZAR');
            $table->string('payment_method')->default('credit_card');
            $table->string('transaction_id')->nullable();
            $table->string('status');
            $table->json('payfast_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};