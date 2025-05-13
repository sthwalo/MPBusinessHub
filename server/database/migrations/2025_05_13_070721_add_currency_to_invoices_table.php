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
    Schema::table('invoices', function (Blueprint $table) {
        $table->string('currency', 3)->default('ZAR')->notNullable();
    });
}

public function down(): void
{
    Schema::table('invoices', function (Blueprint $table) {
        $table->dropColumn('currency');
    });
}
};
